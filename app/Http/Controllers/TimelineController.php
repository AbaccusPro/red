<?php

namespace App\Http\Controllers;

use Alaouy\Youtube\Facades\Youtube;
use App\Announcement;
use App\Category;
use App\Comment;
use App\Group;
use App\Hashtag;
use App\Http\Requests\CreateTimelineRequest;
use App\Http\Requests\UpdateTimelineRequest;
use App\Media;
use App\Notification;
use App\Page;
use App\Post;
use App\Repositories\TimelineRepository;
use App\Role;
use App\Setting;
use App\Timeline;
use App\User;
use Carbon\Carbon;
use DB;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;
use Teepluss\Theme\Facades\Theme;
use Validator;

class TimelineController extends AppBaseController
{
    /** @var TimelineRepository */
    private $timelineRepository;

    public function __construct(TimelineRepository $timelineRepo)
    {
        $this->timelineRepository = $timelineRepo;
    }

    /**
     * Display a listing of the Timeline.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $this->timelineRepository->pushCriteria(new RequestCriteria($request));
        $timelines = $this->timelineRepository->all();

        return view('timelines.index')
            ->with('timelines', $timelines);
    }

    /**
     * Show the form for creating a new Timeline.
     *
     * @return Response
     */
    public function create()
    {
        return view('timelines.create');
    }

    /**
     * Store a newly created Timeline in storage.
     *
     * @param CreateTimelineRequest $request
     *
     * @return Response
     */
    public function store(CreateTimelineRequest $request)
    {
        $input = $request->all();

        $timeline = $this->timelineRepository->create($input);

        Flash::success('Timeline saved successfully.');

        return redirect(route('timelines.index'));
    }

     /**
      * Display the specified Timeline.
      *
      * @param  int $id
      *
      * @return Response
      */
     public function showTimeline($username)
     {
         $admin_role_id = Role::where('name', '=', 'admin')->first();
         $posts = [];
         $timeline = Timeline::where('username', $username)->first();
         $user_post = '';

         if ($timeline == null) {
             return redirect('/');
         }

         $timeline_posts = $timeline->posts()->orderBy('created_at', 'desc')->with('comments')->paginate(Setting::get('items_page'));

         foreach ($timeline_posts as $timeline_post) {
             //This is for filtering reported(flag) posts, displaying non flag posts
            if ($timeline_post->check_reports($timeline_post->id) == false) {
                array_push($posts, $timeline_post);
            }
         }

         if ($timeline->type == 'user') {
             $follow_user_status = '';
             $timeline_post_privacy = '';
             $timeline_post = '';

             $user = User::where('timeline_id', $timeline['id'])->first();
             $own_pages = $user->own_pages();
             $own_groups = $user->own_groups();
             $liked_pages = $user->pageLikes()->get();
             $joined_groups = $user->groups()->get();
             $joined_groups_count = $user->groups()->where('role_id', '!=', $admin_role_id->id)->where('status', '=', 'approved')->get()->count();
             $following_count = $user->following()->where('status', '=', 'approved')->get()->count();
             $followers_count = $user->followers()->where('status', '=', 'approved')->get()->count();
             $followRequests = $user->followers()->where('status', '=', 'pending')->get();

             $follow_user_status = DB::table('followers')->where('follower_id', '=', Auth::user()->id)
                                ->where('leader_id', '=', $user->id)->first();

             if ($follow_user_status) {
                 $follow_user_status = $follow_user_status->status;
             }

             $confirm_follow_setting = $user->getUserSettings(Auth::user()->id);
             $follow_confirm = $confirm_follow_setting->confirm_follow;

           //get user settings
           $live_user_settings = $user->getUserPrivacySettings(Auth::user()->id, $user->id);
             $privacy_settings = explode('-', $live_user_settings);
             $timeline_post = $privacy_settings[0];
             $user_post = $privacy_settings[1];
         } elseif ($timeline->type == 'page') {
             $page = Page::where('timeline_id', '=', $timeline->id)->first();
             $page_members = $page->members();
             $user_post = 'page';
         } elseif ($timeline->type == 'group') {
             $group = Group::where('timeline_id', '=', $timeline->id)->first();
             $group_members = $group->members();
             $user_post = 'group';
         }

         $next_page_url = url('ajax/get-more-posts?page=2&username='.$username);

         $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
         $theme->setTitle($timeline->name.' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

         return $theme->scope('users/timeline', compact('user', 'timeline', 'posts', 'liked_pages', 'timeline_type', 'page', 'group', 'next_page_url', 'joined_groups', 'follow_user_status', 'followRequests', 'following_count', 'followers_count', 'timeline_post', 'user_post', 'follow_confirm', 'joined_groups_count', 'own_pages', 'own_groups', 'group_members', 'page_members'))->render();
     }

    public function getMorePosts(Request $request)
    {
        $timeline = Timeline::where('username', $request->username)->first();

        $posts = $timeline->posts()->orderBy('created_at', 'desc')->with('comments')->paginate(Setting::get('items_page'));
        $theme = Theme::uses('default')->layout('default');

        $responseHtml = '';
        foreach ($posts as $post) {
            $responseHtml .= $theme->partial('post', ['post' => $post, 'timeline' => $timeline, 'next_page_url' => $posts->appends(['username' => $request->username])->nextPageUrl()]);
        }

        return $responseHtml;
    }

    public function showFeed(Request $request)
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');

        $timeline = Timeline::where('username', Auth::user()->username)->first();

        $id = Auth::id();

        $trending_tags = trendingTags();
        $suggested_users = suggestedUsers();
        $suggested_groups = suggestedGroups();
        $suggested_pages = suggestedPages();

        // Check for hashtag
        if ($request->hashtag) {
            $hashtag = '#'.$request->hashtag;

            $posts = Post::where('description', 'like', "%{$hashtag}%")->latest()->paginate(Setting::get('items_page'));
        }
        // else show the normal feed
        else {
            $posts = Post::whereIn('user_id', function ($query) use ($id) {
                $query->select('leader_id')
                    ->from('followers')
                    ->where('follower_id', $id);
            })->orWhere('user_id', $id)->latest()->paginate(Setting::get('items_page'));
        }


        if ($request->ajax) {
            $responseHtml = '';
            foreach ($posts as $post) {
                $responseHtml .= $theme->partial('post', ['post' => $post, 'timeline' => $timeline, 'next_page_url' => $posts->appends(['ajax' => true, 'hashtag' => $request->hashtag])->nextPageUrl()]);
            }

            return $responseHtml;
        }

        $announcement = Announcement::find(Setting::get('announcement'));
        if ($announcement != null) {
            $chk_isExpire = $announcement->chkAnnouncementExpire($announcement->id);

            if ($chk_isExpire == 'notexpired') {
                $active_announcement = $announcement;
                if (!$announcement->users->contains(Auth::user()->id)) {
                    $announcement->users()->attach(Auth::user()->id);
                }
            }
        }


        $next_page_url = url('ajax/get-more-feed?page=2&ajax=true&hashtag='.$request->hashtag.'&username='.Auth::user()->username);

        $theme->setTitle($timeline->name.' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('home', compact('timeline', 'posts', 'next_page_url', 'trending_tags', 'suggested_users', 'active_announcement', 'suggested_groups', 'suggested_pages'))
       ->render();
    }

    public function showGlobalFeed(Request $request)
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');

        $timeline = Timeline::where('username', Auth::user()->username)->first();

        $id = Auth::id();

        $trending_tags = trendingTags();
        $suggested_users = suggestedUsers();
        $suggested_groups = suggestedGroups();
        $suggested_pages = suggestedPages();

        // Check for hashtag
        if ($request->hashtag) {
            $hashtag = '#'.$request->hashtag;

            $posts = Post::where('description', 'like', "%{$hashtag}%")->latest()->paginate(Setting::get('items_page'));
        }
        // else show the normal feed
        else {
            $posts = Post::orderBy('created_at', 'desc')->paginate(Setting::get('items_page'));
        }

        if ($request->ajax) {
            $responseHtml = '';
            foreach ($posts as $post) {
                $responseHtml .= $theme->partial('post', ['post' => $post, 'timeline' => $timeline, 'next_page_url' => $posts->appends(['ajax' => true, 'hashtag' => $request->hashtag])->nextPageUrl()]);
            }

            return $responseHtml;
        }

        $announcement = Announcement::find(Setting::get('announcement'));
        if ($announcement != null) {
            $chk_isExpire = $announcement->chkAnnouncementExpire($announcement->id);

            if ($chk_isExpire == 'notexpired') {
                $active_announcement = $announcement;
                if (!$announcement->users->contains(Auth::user()->id)) {
                    $announcement->users()->attach(Auth::user()->id);
                }
            }
        }

        $next_page_url = url('ajax/get-global-feed?page=2&ajax=true&hashtag='.$request->hashtag.'&username='.Auth::user()->username);

        $theme->setTitle($timeline->name.' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('home', compact('timeline', 'posts', 'next_page_url', 'trending_tags', 'suggested_users', 'active_announcement', 'suggested_groups', 'suggested_pages'))
       ->render();
    }

    public function changeAvatar(Request $request)
    {
        if (Config::get('app.env') == 'demo' && Auth::user()->username == 'bootstrapguru') {
            return response()->json(['status' => '201', 'message' => trans('common.disabled_on_demo')]);
        }
        $timeline = Timeline::where('id', $request->timeline_id)->first();

        if (
        ($request->timeline_type == 'user' && $request->timeline_id == Auth::user()->timeline_id) ||
        ($request->timeline_type == 'page' && $timeline->page->is_admin(Auth::user()->id) == true) ||
        ($request->timeline_type == 'group' && $timeline->groups->is_admin(Auth::user()->id) == true)
        ) {
            if ($request->hasFile('change_avatar')) {
                $timeline_type = $request->timeline_type;

                $change_avatar = $request->file('change_avatar');
                $strippedName = str_replace(' ', '', $change_avatar->getClientOriginalName());
                $photoName = date('Y-m-d-H-i-s').$strippedName;

                // Lets resize the image to the square with dimensions of either width or height , which ever is smaller.
                list($width, $height) = getimagesize($change_avatar->getRealPath());


                $avatar = Image::make($change_avatar->getRealPath());

                if ($width > $height) {
                    $avatar->crop($height, $height);
                } else {
                    $avatar->crop($width, $width);
                }

                $avatar->save(storage_path().'/uploads/'.$timeline_type.'s/avatars/'.$photoName, 60);

                $media = Media::create([
                      'title'  => $photoName,
                      'type'   => 'image',
                      'source' => $photoName,
                    ]);

                $timeline->avatar_id = $media->id;

                if ($timeline->save()) {
                    return response()->json(['status' => '200', 'avatar_url' => url($timeline_type.'/avatar/'.$photoName), 'message' => 'You have successfully updated your avatar']);
                }
            } else {
                return response()->json(['status' => '201', 'message' => 'Updating your avatar failed']);
            }
        }
    }

    public function changeCover(Request $request)
    {
        if (Config::get('app.env') == 'demo' && Auth::user()->username == 'bootstrapguru') {
            return response()->json(['status' => '201', 'message' => trans('common.disabled_on_demo')]);
        }
        if ($request->hasFile('change_cover')) {
            $timeline_type = $request->timeline_type;

            $change_avatar = $request->file('change_cover');
            $strippedName = str_replace(' ', '', $change_avatar->getClientOriginalName());
            $photoName = date('Y-m-d-H-i-s').$strippedName;
            $avatar = Image::make($change_avatar->getRealPath());
            $avatar->save(storage_path().'/uploads/'.$timeline_type.'s/covers/'.$photoName, 60);

            $media = Media::create([
              'title'  => $photoName,
              'type'   => 'image',
              'source' => $photoName,
              ]);

            $timeline = Timeline::where('id', $request->timeline_id)->first();
            $timeline->cover_id = $media->id;

            if ($timeline->save()) {
                return response()->json(['status' => '200', 'cover_url' => url($timeline_type.'/cover/'.$photoName), 'message' => 'You have successfully updated your cover']);
            }
        } else {
            return response()->json(['status' => '201', 'message' => 'Updating your cover failed']);
        }
    }

    public function createPost(Request $request)
    {
        $input = $request->all();

        $input['user_id'] = Auth::user()->id;
        $post = Post::create($input);
        $post->notifications_user()->sync([Auth::user()->id], true);

        if ($request->file('post_images_upload')) {
            foreach ($request->file('post_images_upload') as $postImage) {
                $strippedName = str_replace(' ', '', $postImage->getClientOriginalName());
                $photoName = date('Y-m-d-H-i-s').$strippedName;

                $avatar = Image::make($postImage->getRealPath());

                $avatar->save(storage_path().'/uploads/users/gallery/'.$photoName, 60);

                $media = Media::create([
                      'title'  => $photoName,
                      'type'   => 'image',
                      'source' => $photoName,
                    ]);

                $post->images()->attach($media);
            }
        }

        if ($post) {
            // Check for any mentions and notify them
            preg_match_all('/(^|\s)(@\w+)/', $request->description, $usernames);
            foreach ($usernames[2] as $value) {
                $timeline = Timeline::where('username', str_replace('@', '', $value))->first();
                $notification = Notification::create(['user_id' => $timeline->user->id, 'post_id' => $post->id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' mentioned you in his post', 'type' => 'mention', 'link' => 'post/'.$post->id]);
            }
            $timeline = Timeline::where('id', $request->timeline_id)->first();

            //Notify the user when someone posts on his timeline/page/group

            if ($timeline->type == 'page') {
                $notify_users = $timeline->page->users()->whereNotIn('user_id', [Auth::user()->id])->get();
                $notify_message = 'posted on this page';
            } elseif ($timeline->type == 'group') {
                $notify_users = $timeline->groups->users()->whereNotIn('user_id', [Auth::user()->id])->get();
                $notify_message = 'posted on this group';
            } else {
                $notify_users = $timeline->user()->whereNotIn('id', [Auth::user()->id])->get();
                $notify_message = 'posted on your timeline';
            }

            foreach ($notify_users as $notify_user) {
                Notification::create(['user_id' => $notify_user->id, 'timeline_id' => $request->timeline_id, 'post_id' => $post->id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' '.$notify_message, 'type' => $timeline->type, 'link' => $timeline->username]);
            }


            // Check for any hashtags and save them
            preg_match_all('/(^|\s)(#\w+)/', $request->description, $hashtags);
            foreach ($hashtags[2] as $value) {
                $timeline = Timeline::where('username', str_replace('@', '', $value))->first();
                $hashtag = Hashtag::where('tag', str_replace('#', '', $value))->first();
                if ($hashtag) {
                    $hashtag->count = $hashtag->count + 1;
                    $hashtag->save();
                } else {
                    Hashtag::create(['tag' => str_replace('#', '', $value), 'count' => 1]);
                }
            }

            // Let us tag the post friends :)
            if ($request->user_tags != null) {
                $post->users_tagged()->sync(explode(',', $request->user_tags));
            }
        }

        // $post->users_tagged = $post->users_tagged();
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('ajax');
        $postHtml = $theme->scope('timeline/post', compact('post', 'timeline'))->render();

        return response()->json(['status' => '200', 'data' => $postHtml]);
    }

    public function editPost(Request $request)
    {
        $post = Post::where('id', $request->post_id)->with('user')->first();
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('ajax');
        $postHtml = $theme->partial('edit-post', compact('post'));

        return response()->json(['status' => '200', 'data' => $postHtml]);
    }

    public function loadEmoji()
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('ajax');
        $postHtml = $theme->partial('emoji');

        return response()->json(['status' => '200', 'data' => $postHtml]);
    }

    public function updatePost(Request $request)
    {
        $post = Post::where('id', $request->post_id)->first();
        if ($post->user->id == Auth::user()->id) {
            $post->description = $request->description;
            $post->save();
        }

        return redirect('post/'.$post->id);
    }

    public function getSoundCloudResults(Request $request)
    {
        $soundcloudJson = file_get_contents('http://api.soundcloud.com/tracks.json?client_id='.env('SOUNDCLOUD_CLIENT_ID').'&q='.$request->q);

        return response()->json(['status' => '200', 'data' => $soundcloudJson]);
    }

    public function postComment(Request $request)
    {
        $comment = Comment::create([
                    'post_id'     => $request->post_id,
                    'description' => $request->description,
                    'user_id'     => Auth::user()->id,
                    'parent_id'   => $request->comment_id,
                  ]);

        $post = Post::where('id', $request->post_id)->first();
        $posted_user = $post->user;

        if ($comment) {
            if (Auth::user()->id != $post->user_id) {
                //Notify the user for comment on his/her post
            Notification::create(['user_id' => $post->user_id, 'post_id' => $request->post_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' commented on your post', 'type' => 'comment_post']);
            }

            $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('ajax');
            if ($request->comment_id) {
                $reply = $comment;
                $main_comment = Comment::find($reply->parent_id);
                $main_comment_user = $main_comment->user;

                $user = User::find(Auth::user()->id);
                $user_settings = $user->getUserSettings($main_comment_user->id);
                if ($user_settings && $user_settings->email_reply_comment == 'yes') {
                    Mail::send('emails.commentreply_mail', ['user' => $user, 'main_comment_user' => $main_comment_user], function ($m) use ($user, $main_comment_user) {
                        $m->from(Setting::get('noreply_email'), Setting::get('site_name'));
                        $m->to($main_comment_user->email, $main_comment_user->name)->subject('New reply to your comment');
                    });
                }
                $postHtml = $theme->scope('timeline/reply', compact('reply', 'post'))->render();
            } else {
                $user = User::find(Auth::user()->id);
                $user_settings = $user->getUserSettings($posted_user->id);
                if ($user_settings && $user_settings->email_comment_post == 'yes') {
                    Mail::send('emails.commentmail', ['user' => $user, 'posted_user' => $posted_user], function ($m) use ($user, $posted_user) {
                        $m->from(Setting::get('noreply_email'), Setting::get('site_name'));
                        $m->to($posted_user->email, $posted_user->name)->subject('New comment to your post');
                    });
                }

                $postHtml = $theme->scope('timeline/comment', compact('comment', 'post'))->render();
            }
        }

        return response()->json(['status' => '200', 'comment_id' => $comment->id, 'data' => $postHtml]);
    }

    public function likePost(Request $request)
    {
        $post = Post::findOrFail($request->post_id);
        $posted_user = $post->user;
        $like_count = $post->users_liked()->count();

        //Like the post
        if (!$post->users_liked->contains(Auth::user()->id)) {
            $post->users_liked()->attach(Auth::user()->id, ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            $post->notifications_user()->attach(Auth::user()->id);

            $user = User::find(Auth::user()->id);
            $user_settings = $user->getUserSettings($posted_user->id);
            if ($user_settings && $user_settings->email_like_post == 'yes') {
                Mail::send('emails.postlikemail', ['user' => $user, 'posted_user' => $posted_user], function ($m) use ($posted_user, $user) {
                    $m->from(Setting::get('noreply_email'), Setting::get('site_name'));
                    $m->to($posted_user->email, $posted_user->name)->subject($user->name.' '.'liked your post');
                });
            }

            //Notify the user for post like
            $notify_message = 'liked your post';
            $notify_type = 'like_post';
            $status_message = 'successfully liked';

            if ($post->user->id != Auth::user()->id) {
                Notification::create(['user_id' => $post->user->id, 'post_id' => $post->id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' '.$notify_message, 'type' => $notify_type]);
            }

            return response()->json(['status' => '200', 'liked' => true, 'message' => $status_message, 'likecount' => $like_count]);
        }
        //Unlike the post
        else {
            $post->users_liked()->detach([Auth::user()->id]);
            $post->notifications_user()->detach([Auth::user()->id]);

            //Notify the user for post unlike
            $notify_message = 'unliked your post';
            $notify_type = 'unlike_post';
            $status_message = 'successfully unliked';

            if ($post->user->id != Auth::user()->id) {
                Notification::create(['user_id' => $post->user->id, 'post_id' => $post->id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' '.$notify_message, 'type' => $notify_type]);
            }

            return response()->json(['status' => '200', 'liked' => false, 'message' => $status_message, 'likecount' => $like_count]);
        }

        if ($post) {
            $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('ajax');
            $postHtml = $theme->scope('timeline/post', compact('post'))->render();
        }

        return response()->json(['status' => '200', 'data' => $postHtml]);
    }

    public function likeComment(Request $request)
    {
        $comment = Comment::findOrFail($request->comment_id);
        $comment_user = $comment->user;

        if (!$comment->comments_liked->contains(Auth::user()->id)) {
            $comment->comments_liked()->attach(Auth::user()->id);
            $comment_likes = $comment->comments_liked()->get();
            $like_count = $comment_likes->count();

            //sending email notification
            $user = User::find(Auth::user()->id);
            $user_settings = $user->getUserSettings($comment_user->id);
            if ($user_settings && $user_settings->email_like_comment == 'yes') {
                Mail::send('emails.commentlikemail', ['user' => $user, 'comment_user' => $comment_user], function ($m) use ($user, $comment_user) {
                    $m->from(Setting::get('noreply_email'), Setting::get('site_name'));
                    $m->to($comment_user->email, $comment_user->name)->subject($user->name.' '.'likes your comment');
                });
            }

            //Notify the user for comment like
            if ($comment->user->id != Auth::user()->id) {
                Notification::create(['user_id' => $comment->user_id, 'post_id' => $comment->post_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' liked your comment', 'type' => 'like_comment']);
            }

            return response()->json(['status' => '200', 'liked' => true, 'message' => 'successfully liked', 'likecount' => $like_count]);
        } else {
            $comment->comments_liked()->detach([Auth::user()->id]);
            $comment_likes = $comment->comments_liked()->get();
            $like_count = $comment_likes->count();

            //Notify the user for comment unlike
            if ($comment->user->id != Auth::user()->id) {
                Notification::create(['user_id' => $comment->user_id, 'post_id' => $comment->post_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' unliked your comment', 'type' => 'unlike_comment']);
            }

            return response()->json(['status' => '200', 'unliked' => false, 'message' => 'successfully unliked', 'likecount' => $like_count]);
        }
    }

    public function sharePost(Request $request)
    {
        $post = Post::findOrFail($request->post_id);
        $posted_user = $post->user;

        if (!$post->users_shared->contains(Auth::user()->id)) {
            $post->users_shared()->attach(Auth::user()->id, ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            $post_share_count = $post->users_shared()->get()->count();

            //Notify the user for post share
            Notification::create(['user_id' => $post->user_id, 'post_id' => $request->post_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' shared your post', 'type' => 'share_post']);

            $user = User::find(Auth::user()->id);
            $user_settings = $user->getUserSettings($posted_user->id);

            if ($user_settings && $user_settings->email_post_share == 'yes') {
                Mail::send('emails.postsharemail', ['user' => $user, 'posted_user' => $posted_user], function ($m) use ($user, $posted_user) {
                    $m->from(Setting::get('noreply_email'), Setting::get('site_name'));
                    $m->to($posted_user->email, $posted_user->name)->subject($user->name.' '.'shared your post');
                });
            }

            return response()->json(['status' => '200', 'shared' => true, 'message' => 'successfully shared', 'share_count' => $post_share_count]);
        } else {
            $post->users_shared()->detach([Auth::user()->id]);
            $post_share_count = $post->users_shared()->get()->count();

            //Notify the user for post share
            Notification::create(['user_id' => $post->user_id, 'post_id' => $request->post_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' unshared your post', 'type' => 'unshare_post']);

            return response()->json(['status' => '200', 'unshared' => false, 'message' => 'Successfully unshared', 'share_count' => $post_share_count]);
        }
    }

    public function pageLiked(Request $request)
    {
        $page = Page::where('timeline_id', '=', $request->timeline_id)->first();

        if ($page->likes->contains(Auth::user()->id)) {
            $page->likes()->detach([Auth::user()->id]);

            return response()->json(['status' => '200', 'like' => true, 'message' => 'successfully unliked']);
        }
    }

    public function pageReport(Request $request)
    {
        $timeline = Timeline::where('id', '=', $request->timeline_id)->first();

        if ($timeline->type == 'page') {
            $admins = $timeline->page->admins();
            $report_type = 'page_report';
        }
        if ($timeline->type == 'group') {
            $admins = $timeline->groups->admins();
            $report_type = 'group_report';
        }


        if (!$timeline->reports->contains(Auth::user()->id)) {
            $timeline->reports()->attach(Auth::user()->id, ['status' => 'pending']);

            if ($timeline->type == 'user') {
                Notification::create(['user_id' => $timeline->user->id, 'timeline_id' => $timeline->id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' reported you', 'type' => 'user_report']);
            } else {
                foreach ($admins as $admin) {
                    Notification::create(['user_id' => $admin->id, 'timeline_id' => $timeline->id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' reported your '.$timeline->type, 'type' => $report_type]);
                }
            }


            return response()->json(['status' => '200', 'reported' => true, 'message' => 'successfully reported']);
        } else {
            $timeline->reports()->detach([Auth::user()->id]);

            if ($timeline->type == 'user') {
                Notification::create(['user_id' => $timeline->user->id, 'timeline_id' => $timeline->id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' unreported you', 'type' => 'user_report']);
            } else {
                foreach ($admins as $admin) {
                    Notification::create(['user_id' => $admin->id, 'timeline_id' => $timeline->id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' unreported your page', 'type' => 'page_report']);
                }
            }

            return response()->json(['status' => '200', 'reported' => false, 'message' => 'successfully unreport']);
        }
    }

    public function timelineGroups(Request $request)
    {
        $group = Group::where('timeline_id', '=', $request->timeline_id)->first();

        if ($group->users->contains(Auth::user()->id)) {
            $group->users()->detach([Auth::user()->id]);

            return response()->json(['status' => '200', 'join' => true, 'message' => 'successfully unjoined']);
        }
    }

    public function getYoutubeVideo(Request $request)
    {
        $videoId = Youtube::parseVidFromURL($request->youtube_source);

        $video = Youtube::getVideoInfo($videoId);

        $videoData = [
                        'id'     => $video->id,
                        'title'  => $video->snippet->title,
                        'iframe' => $video->player->embedHtml,
                      ];

        return response()->json(['status' => '200', 'message' => $videoData]);
    }

    public function show($id)
    {
        $timeline = $this->timelineRepository->findWithoutFail($id);

        if (empty($timeline)) {
            Flash::error('Timeline not found');

            return redirect(route('timelines.index'));
        }

        return view('timelines.show')->with('timeline', $timeline);
    }

    /**
     * Show the form for editing the specified Timeline.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $timeline = $this->timelineRepository->findWithoutFail($id);

        if (empty($timeline)) {
            Flash::error('Timeline not found');

            return redirect(route('timelines.index'));
        }

        return view('timelines.edit')->with('timeline', $timeline);
    }

    /**
     * Update the specified Timeline in storage.
     *
     * @param int                   $id
     * @param UpdateTimelineRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateTimelineRequest $request)
    {
        $timeline = $this->timelineRepository->findWithoutFail($id);

        if (empty($timeline)) {
            Flash::error('Timeline not found');

            return redirect(route('timelines.index'));
        }

        $timeline = $this->timelineRepository->update($request->all(), $id);

        Flash::success('Timeline updated successfully.');

        return redirect(route('timelines.index'));
    }

    /**
     * Remove the specified Timeline from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $timeline = $this->timelineRepository->findWithoutFail($id);

        if (empty($timeline)) {
            Flash::error('Timeline not found');

            return redirect(route('timelines.index'));
        }

        $this->timelineRepository->delete($id);

        Flash::success('Timeline deleted successfully.');

        return redirect(route('timelines.index'));
    }

    public function follow(Request $request)
    {
        $follow = User::where('timeline_id', '=', $request->timeline_id)->first();

        if (!$follow->followers->contains(Auth::user()->id)) {
            $follow->followers()->attach(Auth::user()->id, ['status' => 'approved']);

            $user = User::find(Auth::user()->id);
            $user_settings = $user->getUserSettings($follow->id);

            if ($user_settings && $user_settings->email_follow == 'yes') {
                Mail::send('emails.followmail', ['user' => $user, 'follow' => $follow], function ($m) use ($user, $follow) {
                    $m->from(Setting::get('noreply_email'), Setting::get('site_name'));
                    $m->to($follow->email, $follow->name)->subject($user->name.' '.'follows you');
                });
            }

            //Notify the user for follow
            Notification::create(['user_id' => $follow->id, 'timeline_id' => $request->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' is following you', 'type' => 'follow']);

            return response()->json(['status' => '200', 'followed' => true, 'message' => 'successfully followed']);
        } else {
            $follow->followers()->detach([Auth::user()->id]);

            //Notify the user for follow
            Notification::create(['user_id' => $follow->id, 'timeline_id' => $request->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' is unfollowing you', 'type' => 'unfollow']);

            return response()->json(['status' => '200', 'followed' => false, 'message' => 'successfully unFollowed']);
        }
    }

    public function joiningGroup(Request $request)
    {
        $user_role_id = Role::where('name', '=', 'user')->first();
        $group = Group::where('timeline_id', '=', $request->timeline_id)->first();
        $group_timeline = $group->timeline;

        $users = $group->users()->get();

        if (!$group->users->contains(Auth::user()->id)) {
            $group->users()->attach(Auth::user()->id, ['role_id' => $user_role_id->id, 'status' => 'approved']);


            foreach ($users as $user) {
                if ($user->id != Auth::user()->id) {
                    //Notify the user for page like
                  Notification::create(['user_id' => $user->id, 'timeline_id' => $request->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' joined your group', 'type' => 'join_group']);
                }

                if ($group->is_admin($user->id)) {
                    $group_admin = User::find($user->id);
                    $user = User::find(Auth::user()->id);
                    $user_settings = $user->getUserSettings($group_admin->id);
                    if ($user_settings && $user_settings->email_join_group == 'yes') {
                        Mail::send('emails.groupjoinmail', ['user' => $user, 'group_timeline' => $group_timeline], function ($m) use ($user, $group_admin, $group_timeline) {
                            $m->from(Setting::get('noreply_email'), Setting::get('site_name'));
                            $m->to($group_admin->email)->subject($user->name.' '.'joined your group');
                        });
                    }
                }
            }

            return response()->json(['status' => '200', 'joined' => true, 'message' => 'successfully joined']);
        } else {
            $group->users()->detach([Auth::user()->id]);

            foreach ($users as $user) {
                if ($user->id != Auth::user()->id) {
                    //Notify the user for page like
                  Notification::create(['user_id' => $user->id, 'timeline_id' => $request->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' unjoined your group', 'type' => 'unjoin_group']);
                }
            }

            return response()->json(['status' => '200', 'joined' => false, 'message' => 'successfully unjoined']);
        }
    }

    public function joiningClosedGroup(Request $request)
    {
        $user_role_id = Role::where('name', '=', 'user')->first();
        $group = Group::where('timeline_id', '=', $request->timeline_id)->first();

        if (!$group->users->contains(Auth::user()->id)) {
            $group->users()->attach(Auth::user()->id, ['role_id' => $user_role_id->id, 'status' => 'pending']);


            $users = $group->users()->get();
            foreach ($users as $user) {
                if (Auth::user()->id != $user->id) {
                    //Notify the user for page like
                  Notification::create(['user_id' => $user->id, 'timeline_id' => $request->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' requested to join your group', 'type' => 'group_join_request']);
                }
            }

            return response()->json(['status' => '200', 'joinrequest' => true, 'message' => 'successfully sent group join request']);
        } else {
            $checkStatus = $group->chkGroupUser($group->id, Auth::user()->id);

            if ($checkStatus && $checkStatus->status == 'approved') {
                $group->users()->detach([Auth::user()->id]);

                return response()->json(['status' => '200', 'join' => true, 'message' => 'unsuccessfully request']);
            } else {
                $group->users()->detach([Auth::user()->id]);

                return response()->json(['status' => '200', 'joinrequest' => false, 'message' => 'unsuccessfully request']);
            }
        }
    }

    public function userFollowRequest(Request $request)
    {
        $user = User::where('timeline_id', '=', $request->timeline_id)->first();

        if (!$user->followers->contains(Auth::user()->id)) {
            $user->followers()->attach(Auth::user()->id, ['status' => 'pending']);

            //Notify the user for page like
            Notification::create(['user_id' => $user->id, 'timeline_id' => Auth::user()->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' requested you to follow', 'type' => 'follow_requested']);

            return response()->json(['status' => '200', 'followrequest' => true, 'message' => 'successfully sent user follow request']);
        } else {
            if ($request->follow_status == 'approved') {
                $user->followers()->detach([Auth::user()->id]);

                return response()->json(['status' => '200', 'unfollow' => true, 'message' => 'unfollowed successfully']);
            } else {
                $user->followers()->detach([Auth::user()->id]);

                return response()->json(['status' => '200', 'followrequest' => false, 'message' => 'unsuccessfully request']);
            }
        }
    }

    public function pageLike(Request $request)
    {
        $page = Page::where('timeline_id', '=', $request->timeline_id)->first();
        $page_timeline = $page->timeline;

        if (!$page->likes->contains(Auth::user()->id)) {
            $page->likes()->attach(Auth::user()->id);

            if (!$page->users->contains(Auth::user()->id)) {
                $users = $page->users()->get();
                foreach ($users as $user) {
                    //Notify the user for page like
                Notification::create(['user_id' => $user->id, 'timeline_id' => $request->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' liked your page', 'type' => 'like_page']);

                    if ($page->is_admin($user->id)) {
                        $page_admin = User::find($user->id);
                        $user = User::find(Auth::user()->id);
                        $user_settings = $user->getUserSettings($page_admin->id);
                        if ($user_settings && $user_settings->email_like_page == 'yes') {
                            Mail::send('emails.pagelikemail', ['user' => $user, 'page_timeline' => $page_timeline], function ($m) use ($user, $page_admin, $page_timeline) {
                                $m->from(Setting::get('noreply_email'), Setting::get('site_name'));
                                $m->to($page_admin->email)->subject($user->name.' '.'liked your page');
                            });
                        }
                    }
                }
            }

            return response()->json(['status' => '200', 'liked' => true, 'message' => 'Page successfully liked']);
        } else {
            $page->likes()->detach([Auth::user()->id]);

            if (!$page->users->contains(Auth::user()->id)) {
                $users = $page->users()->get();
                foreach ($users as $user) {
                    //Notify the user for page unlike
                Notification::create(['user_id' => $user->id, 'timeline_id' => $request->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' unliked your page', 'type' => 'unlike_page']);
                }
            }

            return response()->json(['status' => '200', 'liked' => false, 'message' => 'Page successfully unliked']);
        }
    }

    public function getNotifications(Request $request)
    {
        $post = Post::findOrFail($request->post_id);

        if (!$post->notifications_user->contains(Auth::user()->id)) {
            $post->notifications_user()->attach(Auth::user()->id);

            return response()->json(['status' => '200', 'notified' => true, 'message' => 'Successfull']);
        } else {
            $post->notifications_user()->detach([Auth::user()->id]);

            return response()->json(['status' => '200', 'unnotify' => false, 'message' => 'UnSuccessfull']);
        }
    }

    public function addPage($username)
    {
        $category_options = ['' => 'Select Category'] + Category::active()->lists('name', 'id')->all();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');

        $theme->setTitle(trans('common.create_page').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('timeline/create-page', compact('username', 'category_options'))->render();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => 'required|max:30|min:5',
            'username' => 'required|max:26|min:5|alpha_num|unique:timelines',
            'category' => 'required',
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function groupPageValidator(array $data)
    {
        return Validator::make($data, [
            'name'     => 'required',
            'username' => 'required|max:16|min:5|alpha_num|unique:timelines',
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function groupPageSettingsValidator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required',
        ]);
    }

    public function createPage(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
                  ->withInput($request->all())
                  ->withErrors($validator->errors());
        }

        //Create timeline record for userpage
        $timeline = Timeline::create([
            'username' => $request->username,
            'name'     => $request->name,
            'about'    => $request->about,
            'type'     => 'page',
            ]);

        $page = Page::create([
            'timeline_id'           => $timeline->id,
            'category_id'           => $request->category,
            'member_privacy'        => Setting::get('page_member_privacy'),
            'timeline_post_privacy' => Setting::get('page_timeline_post_privacy'),
            ]);

        $role = Role::where('name', '=', 'Admin')->first();
        //below code inserting record in to page_user table
        $page->users()->attach(Auth::user()->id, ['role_id' => $role->id, 'active' => 1]);
        $message = 'Page created successfully';
        $username = $request->username;

        return redirect('/'.$username);
    }

    public function addGroup($username)
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.create_group').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('timeline/create-group', compact('username'))->render();
    }

    public function posts($username)
    {
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $timeline = Timeline::where('username', $username)->first();
        $posts = $timeline->posts()->orderBy('created_at', 'desc')->with('comments')->paginate(Setting::get('items_page'));


        if ($timeline->type == 'user') {
            $follow_user_status = '';
            $user = User::where('timeline_id', $timeline['id'])->first();
            $followRequests = $user->followers()->where('status', '=', 'pending')->get();
            $liked_pages = $user->pageLikes()->get();
            $joined_groups = $user->groups()->get();
            $own_pages = $user->own_pages();
            $own_groups = $user->own_groups();
            $following_count = $user->following()->where('status', '=', 'approved')->get()->count();
            $followers_count = $user->followers()->where('status', '=', 'approved')->get()->count();
            $joined_groups_count = $user->groups()->where('role_id', '!=', $admin_role_id->id)->where('status', '=', 'approved')->get()->count();
            $follow_user_status = DB::table('followers')->where('follower_id', '=', Auth::user()->id)
                                ->where('leader_id', '=', $user->id)->first();

            if ($follow_user_status) {
                $follow_user_status = $follow_user_status->status;
            }

            $confirm_follow_setting = $user->getUserSettings(Auth::user()->id);
            $follow_confirm = $confirm_follow_setting->confirm_follow;

            $live_user_settings = $user->getUserPrivacySettings(Auth::user()->id, $user->id);
            $privacy_settings = explode('-', $live_user_settings);
            $timeline_post = $privacy_settings[0];
            $user_post = $privacy_settings[1];
        } else {
            $user = User::where('id', Auth::user()->id)->first();
        }

        $next_page_url = url('ajax/get-more-posts?page=2&username='.$username);

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.posts').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));


        return $theme->scope('timeline/posts', compact('timeline', 'user', 'posts', 'liked_pages', 'followRequests', 'joined_groups', 'own_pages', 'own_groups', 'follow_user_status', 'following_count', 'followers_count', 'follow_confirm', 'user_post', 'timeline_post', 'joined_groups_count', 'next_page_url'))->render();
    }

    public function createGroupPage(Request $request)
    {
        $validator = $this->groupPageValidator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }

        //Create timeline record for userpage
        $timeline = Timeline::create([
            'username' => $request->username,
            'name'     => $request->name,
            'about'    => $request->about,
            'type'     => 'group',
            ]);

        if ($request->type == 'open') {
            $group = Group::create([
            'timeline_id'    => $timeline->id,
            'type'           => $request->type,
            'active'         => 1,
            'member_privacy' => 'everyone',
            'post_privacy'   => 'everyone',
            ]);
        } else {
            $group = Group::create([
                'timeline_id'    => $timeline->id,
                'type'           => $request->type,
                'active'         => 1,
                'member_privacy' => Setting::get('group_member_privacy'),
                'post_privacy'   => Setting::get('group_timeline_post_privacy'),
                ]);
        }
        $role = Role::where('name', '=', 'Admin')->first();
        //below code inserting record in to page_user table
        if ($request->type == 'open' || $request->type == 'closed' || $request->type == 'secret') {
            $group->users()->attach(Auth::user()->id, ['role_id' => $role->id, 'status' => 'approved']);
        } else {
            $group->users()->attach(Auth::user()->id, ['role_id' => $role->id]);
        }

        $message = 'Page created successfully';
        $username = $request->username;

        return redirect('/'.$username);
    }

    public function pagesGroups($username)
    {
        $timeline = Timeline::where('username', $username)->with('user')->first();
        if ($timeline == null) {
            return redirect('/');
        }
        if ($timeline->id == Auth::user()->timeline_id) {
            $user = $timeline->user;
            $userPages = $user->own_pages();
            $groupPages = $user->own_groups();
            $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
            $theme->setTitle('Pages & Groups | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

            return $theme->scope('timeline/pages-groups', compact('userPages', 'groupPages'))->render();
        } else {
            return redirect($timeline->username);
        }
    }

    public function generalPageSettings($username)
    {
        $timeline = Timeline::where('username', $username)->with('page')->first();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.general_settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('page/settings/general', compact('timeline', 'username'))->render();
    }

    public function updateGeneralPageSettings(Request $request)
    {
        $validator = $this->groupPageSettingsValidator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
                  ->withInput($request->all())
                  ->withErrors($validator->errors());
        }
        $timeline = Timeline::where('username', $request->username)->first();
        $timeline_values = $request->only('username', 'name', 'about');
        $update_timeline = $timeline->update($timeline_values);

        $page = Page::where('timeline_id', $timeline->id)->first();
        $page_values = $request->only('address', 'phone', 'website');
        $update_page = $page->update($page_values);


        Flash::success('General settings updated successfully.');

        return redirect()->back();
    }

    public function privacyPageSettings($username)
    {
        $timeline = Timeline::where('username', $username)->first();
        $page_details = $timeline->page()->first();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.privacy_settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('page/settings/privacy', compact('timeline', 'username', 'page_details'))->render();
    }

    public function updatePrivacyPageSettings(Request $request)
    {
        $timeline = Timeline::where('username', $request->username)->first();
        $page = Page::where('timeline_id', $timeline->id)->first();
        $page->timeline_post_privacy = $request->timeline_post_privacy;
        $page->member_privacy = $request->member_privacy;
        $page->save();

        Flash::success('Privacy settings updated successfully.');

        return redirect()->back();
    }

    public function rolesPageSettings($username)
    {
        $timeline = Timeline::where('username', $username)->first();
        $page = $timeline->page;
        $page_members = $page->members();
        $roles = Role::lists('name', 'id');

        $theme = Theme::uses('default')->layout('default');
        $theme->setTitle(trans('common.manage_roles').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('page/settings/roles', compact('timeline', 'page_members', 'roles', 'page'))->render();
    }

    public function likesPageSettings($username)
    {
        $timeline = Timeline::where('username', $username)->with('page')->first();
        $page_likes = $timeline->page->likes()->where('user_id', '!=', Auth::user()->id)->get();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.page_likes').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('page/settings/likes', compact('timeline', 'page_likes'))->render();
    }

    public function userGroupSettings($username)
    {
        $timeline = Timeline::where('username', $username)->first();

        $group_details = $timeline->groups()->first();

        $group = Group::where('timeline_id', '=', $timeline->id)->first();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.group_settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('group/settings', compact('timeline', 'username', 'group_details'))->render();
    }

   //Getting group members
    public function getGroupMember($username, $group_id)
    {
        $timeline = Timeline::where('username', $username)->with('groups')->first();
        $group = $timeline->groups;
        $group_members = $group->members();

        $member_role_options = Role::lists('name', 'id');

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.members').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/members', compact('timeline', 'group_members', 'group', 'group_id', 'member_role_options'))->render();
    }

    //Displaying group admins
    public function getAdminMember($username, $group_id)
    {
        $timeline = Timeline::where('username', $username)->with('groups')->first();
        $group = $timeline->groups;
        $group_admins = $group->admins();
        $group_members = $group->members();
        $member_role_options = Role::lists('name', 'id');

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.admins').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/admin-group-member', compact('timeline', 'group', 'group_id', 'group_admins', 'member_role_options', 'group_members'))->render();
    }

    //Displaying group members posts
    public function getGroupPosts($username, $group_id)
    {
        $timeline = Timeline::where('username', $username)->with('groups')->first();
        $posts = $timeline->posts()->orderBy('created_at', 'desc')->with('comments')->get();
        $group = $timeline->groups;
        $group_members = $group->members();
        $next_page_url = url('ajax/get-more-posts?page=2&username='.$username);

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.posts').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('timeline/groupposts', compact('timeline', 'group', 'posts', 'group_members', 'next_page_url'))->render();
    }

    public function getJoinRequests($username, $group_id)
    {
        $group = Group::findOrFail($group_id);
        $requestedUsers = $group->pending_members();
        $timeline = Timeline::where('username', $username)->first();
        $group_members = $group->members();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.join_requests').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/joinrequests', compact('timeline', 'username', 'requestedUsers', 'group_id', 'group', 'group_members'))->render();
    }

    //Getting page members with count whose status approved
    public function getPageMember($username)
    {
        $timeline = Timeline::where('username', $username)->with('page')->first();
        $page = $timeline->page;
        $page_members = $page->members();
        $roles = Role::lists('name', 'id');

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.members').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/pagemembers', compact('timeline', 'page', 'roles', 'page_members'))->render();
    }

    //Displaying admin of the page
    public function getPageAdmins($username)
    {
        $timeline = Timeline::where('username', $username)->with('page')->first();
        $page = $timeline->page;
        $page_admins = $page->admins();
        $page_members = $page->members();
        $roles = Role::lists('name', 'id');

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.admins').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/pageadmins', compact('timeline', 'page', 'page_admins', 'roles', 'page_members'))->render();
    }

    // Displaying page likes
    public function getPageLikes($username)
    {
        $timeline = Timeline::where('username', $username)->with('page', 'page.likes', 'page.users')->first();
        $page = $timeline->page;
        $page_likes = $page->likes()->get();
        $page_members = $page->members();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.page_likes').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('timeline/page_likes', compact('timeline', 'page', 'page_likes', 'page_members'))->render();
    }

    //Displaying page members posts
    public function getPagePosts($username)
    {
        $page_user_id = '';
        $timeline = Timeline::where('username', $username)->with('page', 'page.likes', 'page.users')->first();
        $page = $timeline->page;
        $posts = $timeline->posts()->orderBy('created_at', 'desc')->with('comments')->get();
        $page_members = $page->members();
        $next_page_url = url('ajax/get-more-posts?page=2&username='.$username);

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.posts').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('timeline/pageposts', compact('timeline', 'posts', 'page', 'page_user_id', 'page_members', 'next_page_url'))->render();
    }

    //Assigning role for a member in group
    public function assignMemberRole(Request $request)
    {
        $chkUser_exists = '';
        $group = Group::findOrFail($request->group_id);
        $chkUser_exists = $group->chkGroupUser($request->group_id, $request->user_id);
        if ($chkUser_exists) {
            $result = $group->updateMemberRole($request->member_role, $request->group_id, $request->user_id);
            if ($result) {
                Flash::success('Role assigned successfully');

                return redirect()->back();
            } else {
                Flash::success('Role assigned successfully');

                return redirect()->back();
            }
        }
    }

    //Assigning role for a member in page
    public function assignPageMemberRole(Request $request)
    {
        $chkUser_exists = '';
        $page = Page::findOrFail($request->page_id);

        $chkUser_exists = $page->chkPageUser($request->page_id, $request->user_id);

        if ($chkUser_exists) {
            $result = $page->updatePageMemberRole($request->member_role, $request->page_id, $request->user_id);
            if ($result) {
                Flash::success('Role assigned successfully');

                return redirect()->back();
            } else {
                Flash::success('Role assigned successfully');

                return redirect()->back();
            }
        }
    }

    //Removing member from group
    public function removeGroupMember(Request $request)
    {
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $chkUser_exists = '';
        $group = Group::findOrFail($request->group_id);

        $group_admins = $group->users()->where('group_id', $group->id)->where('role_id', '=', $admin_role_id->id)->get()->count();
        $group_members = $group->users()->where('group_id', $group->id)->where('user_id', '=', $request->user_id)->first();

        if ($group_members->pivot->role_id == $admin_role_id->id && $group_admins > 1) {
            $chkUser_exists = $group->removeMember($request->group_id, $request->user_id);
        } elseif ($group_members->pivot->role_id != $admin_role_id->id) {
            $chkUser_exists = $group->removeMember($request->group_id, $request->user_id);
        }

        if ($chkUser_exists) {
            if (Auth::user()->id != $request->user_id) {
                //Notify the user for accepting group's join request
            Notification::create(['user_id' => $request->user_id, 'timeline_id' => $group->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' removed you from the group', 'type' => 'remove_group_member']);
            }

            return response()->json(['status' => '200', 'deleted' => true, 'message' => 'Member successfully removed from group']);
        } else {
            return response()->json(['status' => '200', 'deleted' => false, 'message' => 'Assign admin role for member and remove']);
        }
    }

    //Removing member from page
    public function removePageMember(Request $request)
    {
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $chkUser_exists = '';
        $page = Page::findOrFail($request->page_id);

        $page_admins = $page->users()->where('page_id', $page->id)->where('role_id', '=', $admin_role_id->id)->get()->count();
        $page_members = $page->users()->where('page_id', $page->id)->where('user_id', '=', $request->user_id)->first();

        if ($page_members->pivot->role_id == $admin_role_id->id && $page_admins > 1) {
            $chkUser_exists = $page->removePageMember($request->page_id, $request->user_id);
        } elseif ($page_members->pivot->role_id != $admin_role_id->id) {
            $chkUser_exists = $page->removePageMember($request->page_id, $request->user_id);
        }
          // else{
          //     return response()->json(['status' => '200','deleted' => false,'message'=>'Assign admin role for member and remove']);
          // }

        if ($chkUser_exists) {
            if (Auth::user()->id != $request->user_id) {
                //Notify the user for accepting page's join request
            Notification::create(['user_id' => $request->user_id, 'timeline_id' => $page->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' removed you from the page', 'type' => 'remove_page_member']);
            }

            return response()->json(['status' => '200', 'deleted' => true, 'message' => 'Member successfully removed from page']);
        } else {
            return response()->json(['status' => '200', 'deleted' => false, 'message' => 'Assign admin role for member and remove']);
        }
    }

    public function acceptJoinRequest(Request $request)
    {
        $group = Group::findOrFail($request->group_id);

        $chkUser = $group->chkGroupUser($request->group_id, $request->user_id);


        if ($chkUser) {
            $group_user = $group->updateStatus($chkUser->id);

            if ($group_user) {

                //Notify the user for accepting group's join request
                Notification::create(['user_id' => $request->user_id, 'timeline_id' => $group->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' accepted your join request', 'type' => 'accept_group_join']);
            }

            Flash::success('Request Accepted');

            return response()->json(['status' => '200', 'accepted' => true, 'message' => 'Join request successfully accepted']);
        }
    }

    public function rejectJoinRequest(Request $request)
    {
        $group = Group::findOrFail($request->group_id);
        $chkUser = $group->chkGroupUser($request->group_id, $request->user_id);

        if ($chkUser) {
            $group_user = $group->decilneRequest($chkUser->id);
            if ($group_user) {

              //Notify the user for rejected group's join request
                Notification::create(['user_id' => $request->user_id, 'timeline_id' => $group->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' rejected your join request', 'type' => 'reject_group_join']);
            }

            Flash::success('Request Rejected');

            return response()->json(['status' => '200', 'rejected' => true, 'message' => 'Join request successfully rejected']);
        }
    }

    public function updateUserGroupSettings(Request $request, $username)
    {
        $validator = $this->groupPageSettingsValidator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
                  ->withInput($request->all())
                  ->withErrors($validator->errors());
        }

        $timeline = Timeline::where('username', $username)->first();
        $timeline->username = $username;
        $timeline->name = $request->name;
        $timeline->about = $request->about;
        $timeline->save();

        $group = Group::where('timeline_id', $timeline->id)->first();
        $group->type = $request->type;
        $group->member_privacy = $request->member_privacy;
        $group->post_privacy = $request->post_privacy;
        $group->save();

        Flash::success('Group settings updated successfully.');

        return redirect()->back();
    }

    public function deleteComment(Request $request)
    {
        $comment = Comment::where('id', '=', $request->comment_id)->first();

        if ($comment->delete()) {
            if (Auth::user()->id != $comment->user->id) {
                //Notify the user for comment delete
              Notification::create(['user_id' => $comment->user->id, 'post_id' => $comment->post_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' deleted your comment', 'type' => 'delete_comment']);
            }

            return response()->json(['status' => '200', 'deleted' => true, 'message' => 'Comment successfully deleted']);
        } else {
            return response()->json(['status' => '200', 'notdeleted' => false, 'message' => 'Unsuccessfull']);
        }
    }

    public function deletePage(Request $request)
    {
        $page = Page::where('id', '=', $request->page_id)->first();

        if ($page->delete()) {
            $users = $page->users()->get();
            foreach ($users as $user) {
                if ($user->id != Auth::user()->id) {
                    //Notify the user for page delete
                Notification::create(['user_id' => $user->id, 'timeline_id' => $page->timeline->id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' deleted your page', 'type' => 'delete_page']);
                }
            }

            return response()->json(['status' => '200', 'deleted' => true, 'message' => 'Page successfully deleted']);
        } else {
            return response()->json(['status' => '200', 'notdeleted' => false, 'message' => 'Unsuccessful']);
        }
    }

    public function deletePost(Request $request)
    {
        $post = Post::where('id', '=', $request->post_id)->first();

        if ($post->user->id == Auth::user()->id) {
            $postDeleted = $post->delete();

            return response()->json(['status' => '200', 'deleted' => true, 'message' => 'Page successfully deleted']);
        } else {
            return response()->json(['status' => '200', 'notdeleted' => false, 'message' => 'Unsuccessful']);
        }
    }

    public function reportPost(Request $request)
    {
        $post = Post::where('id', '=', $request->post_id)->first();
        $reported = $post->managePostReport($request->post_id, Auth::user()->id);

        if ($reported) {
            //Notify the user for reporting his post
          Notification::create(['user_id' => $post->user_id, 'post_id' => $request->post_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' reported your post', 'type' => 'report_post']);

            return response()->json(['status' => '200', 'reported' => true, 'message' => 'Post successfully reported']);
        }
    }

    public function singlePost($post_id)
    {
        $post = Post::where('id', '=', $post_id)->first();

        $trending_tags = trendingTags();
        $suggested_users = suggestedUsers();
        $suggested_groups = suggestedGroups();
        $suggested_pages = suggestedPages();

        //Redirect to home page if post doesn't exist
        if ($post == null) {
            return redirect('/');
        }
        $theme = Theme::uses('default')->layout('default');
        $theme->setTitle(trans('common.post').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('timeline/single-post', compact('post', 'suggested_users', 'trending_tags', 'suggested_groups', 'suggested_pages'))->render();
    }
}
