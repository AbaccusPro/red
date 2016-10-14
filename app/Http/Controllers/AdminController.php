<?php

namespace App\Http\Controllers;

use App\Announcement;
use App\Category;
use App\Comment;
use App\Group;
use App\Notification;
use App\Page;
use App\Post;
use App\Setting;
use App\StaticPage;
use App\Timeline;
use App\User;
use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use File;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Teepluss\Theme\Facades\Theme;
use Validator;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('disabledemo', ['only' => [
                        'storeCustomPage',
                        'updateCustomPage',
                        'updateGeneralSettings',
                        'updateUserSettings',
                        'updatePageSettings',
                        'updateGroupSettings',
                        'updateAnnouncement',
                        'addAnnouncements',
                        'removeAnnouncement',
                        'activeAnnouncement',
                        'updateUser',
                        'updatePassword',
                        'deleteUser',
                        'updatePage',
                        'deletePage',
                        'updateGroup',
                        'deleteGroup',
                        'markSafeReports',
                        'deletePostReports',
                        'updateManageAds',
                        'markPageSafeReports',
                        'deletePageReports',
                        'deleteGroupReports',
                        'deleteUserReports',
                        'saveEnv',
                        'updateCategory',
                        'removeCategory',
                        'storeCategory',
                        'postUpdateDatabase',
                    ],
            ]);
    }

    public function dashboard()
    {
        //User registered
        $users = Auth::user()->get();
        $dashboard_user_results = $this->getDashboard($users);
        $result = explode('-', $dashboard_user_results);
        $today_user_count = $result[0];
        $month_user_count = $result[1];
        $year_user_count = $result[2];
        $total_user_count = count($users);

        //Pages Created
        $pages = Page::get();
        $dashboard_page_results = $this->getDashboard($pages);
        $result = explode('-', $dashboard_page_results);
        $today_page_count = $result[0];
        $month_page_count = $result[1];
        $year_page_count = $result[2];
        $total_page_count = count($pages);

        //Groups Created
        $groups = Group::get();
        $dashboard_group_results = $this->getDashboard($groups);
        $result = explode('-', $dashboard_group_results);
        $today_group_count = $result[0];
        $month_group_count = $result[1];
        $year_group_count = $result[2];
        $total_group_count = count($groups);

        //Comments Posted
        $comments = Comment::get();
        $dashboard_comment_results = $this->getDashboard($comments);
        $result = explode('-', $dashboard_comment_results);
        $today_comment_count = $result[0];
        $month_comment_count = $result[1];
        $year_comment_count = $result[2];
        $total_comment_count = count($comments);

        //Stories posted
        $posts = Post::get();
        $dashboard_post_results = $this->getDashboard($posts);
        $result = explode('-', $dashboard_post_results);
        $today_post_count = $result[0];
        $month_post_count = $result[1];
        $year_post_count = $result[2];
        $total_post_count = count($posts);

        //Posts Liked
        $post = new Post();
        $postLikes = $post->postsLiked();
        $dashboard_like_results = $this->getDashboard($postLikes);
        $result = explode('-', $dashboard_like_results);
        $today_like_count = $result[0];
        $month_like_count = $result[1];
        $year_like_count = $result[2];
        $total_like_count = count($postLikes);

        //Posts Reported
        $postReports = $post->postsReported();
        $dashboard_report_results = $this->getDashboard($postReports);
        $result = explode('-', $dashboard_report_results);
        $today_report_count = $result[0];
        $month_report_count = $result[1];
        $year_report_count = $result[2];
        $total_report_count = count($postReports);

        //Stories Shared
        $postShared = $post->postShared();
        $dashboard_shared_results = $this->getDashboard($postShared);
        $result = explode('-', $dashboard_shared_results);
        $today_shared_count = $result[0];
        $month_shared_count = $result[1];
        $year_shared_count = $result[2];
        $total_shared_count = count($postShared);

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.dashboard').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/dashboard', compact('today_user_count', 'month_user_count', 'year_user_count', 'total_user_count', 'today_page_count',
            'month_page_count', 'year_page_count', 'total_page_count', 'today_group_count', 'month_group_count', 'year_group_count', 'total_group_count',
            'today_comment_count', 'month_comment_count', 'year_comment_count', 'total_comment_count', 'today_like_count', 'month_like_count', 'year_like_count',
            'total_like_count', 'today_report_count', 'month_report_count', 'year_report_count', 'total_report_count', 'today_post_count', 'month_post_count',
            'year_post_count', 'total_post_count', 'today_shared_count', 'month_shared_count', 'year_shared_count', 'total_shared_count'))->render();
    }

    public function getDashboard($data_args)
    {
        $current_date = date('Y-m-d', strtotime(Carbon::now()));
        $current_month = date('Y-m', strtotime(Carbon::now()));
        $current_year = date('Y', strtotime(Carbon::now()));
        $today_user_count = 0;
        $month_user_count = 0;
        $year_user_count = 0;

        foreach ($data_args as $data_arg) {
            if ($current_date == date('Y-m-d', strtotime($data_arg->created_at))) {
                $today_user_count++;
            }

            if ($current_month == date('Y-m', strtotime($data_arg->created_at))) {
                $month_user_count++;
            }

            if ($current_year == date('Y', strtotime($data_arg->created_at))) {
                $year_user_count++;
            }
        }

        return $today_user_count.'-'.$month_user_count.'-'.$year_user_count;
    }

    public function generalSettings()
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.general_settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/general-settings')->render();
    }

    public function listCustomPages()
    {
        $staticpages = StaticPage::all();
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.custom_pages').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/custompageindex', compact('staticpages'))->render();
    }

    public function createCustomPage()
    {
        $mode = 'create';
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('admin.create_custom_page').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/custom-pages', compact('mode'))->render();
    }

    public function editCustomPage($id)
    {
        $mode = 'edit';
        $staticPage = StaticPage::find($id);
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.edit').' '.$staticPage->title.' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/custom-pages', compact('mode', 'staticPage'))->render();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function customPageValidator(array $data)
    {
        return Validator::make($data, [
            'title'       => 'required|max:30|min:5',
            'description' => 'required',
        ]);
    }

    public function storeCustomPage(Request $request)
    {
        $mode = 'create';
        $staticPage = new StaticPage();
        $validation = Validator::make(
            $request->only('title', 'description'),
            [
                'title'       => ['required'],
                'description' => ['required'],
            ]
        );

        if ($validation->passes()) {
            $page = StaticPage::create($request->all());
            Flash::success(trans('messages.page_created_success'));
            // $staticpages = StaticPage::all();
            $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

            return $theme->scope('admin/custom-pages', compact('mode'))->render();
        } else {
            $errors = $validation->messages();

            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($errors);
        }
    }

    public function updateCustomPage(Request $request, $id)
    {
        $validator = $this->customPageValidator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }

        $staticPage = StaticPage::find($id);
        $staticPage->title = $request->title;
        $staticPage->description = $request->description;
        $staticPage->active = $request->active;
        $staticPage->save();

        Flash::success(trans('messages.page_updated_success'));

        return redirect()->back();
    }

    public function updateGeneralSettings(Request $request)
    {
        $settings = $request->except('_token');

        $change_logo = $request->file('logo');
        if ($change_logo) {
            $photoName = 'logo.jpg';
            $logo = Image::make($change_logo->getRealPath());

            $logo->save(storage_path().'/uploads/settings/'.$photoName, 60);
            $settings['logo'] = $photoName;
        }
        $change_favicon = $request->file('favicon');
        if ($change_favicon) {
            $photoName = 'favicon.jpg';
            $favicon = Image::make($change_favicon->getRealPath());

            $favicon->save(storage_path().'/uploads/settings/'.$photoName, 60);
            $settings['favicon'] = $photoName;
        }
        Setting::set($settings);

        $language_options = ['' => 'Select Language'] + Config::get('app.locales');

        Flash::success(trans('messages.settings_updated_success'));
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        return $theme->scope('admin/general-settings', compact('language_options', 'settings'))->render();
    }

    public function userSettings()
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.user_settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/user-settings')->render();
    }

    public function updateUserSettings(Request $request)
    {
        $settings = $request->except('_token');

        Setting::set($settings);
        Flash::success(trans('messages.user_settings_updated_success'));
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        return $theme->scope('admin/user-settings')->render();
    }

    public function pageSettings()
    {
        $categories = Category::paginate(10);

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.page_settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/page-settings', compact('categories'))->render();
    }

    public function updatePageSettings(Request $request)
    {
        $settings = $request->except('_token');

        Setting::set($settings);
        Flash::success(trans('messages.page_settings_updated_success'));
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        return $theme->scope('admin/page-settings')->render();
    }

    public function groupSettings()
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.group_settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/group-settings')->render();
    }

    public function updateGroupSettings(Request $request)
    {
        $settings = $request->except('_token');

        Setting::set($settings);
        Flash::success(trans('messages.group_settings_updated_success'));
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        return $theme->scope('admin/group-settings')->render();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function announcementValidator(array $data)
    {
        return Validator::make($data, [
            'title'       => 'required|max:30|min:5',
            'description' => 'required',
            'start_date'  => 'required',
            'end_date'    => 'required',
        ]);
    }

    public function getAnnouncements()
    {
        $total_days = '';
        $announcements = Announcement::paginate(10);
        $current_anouncement = Announcement::find(Setting::get('announcement'));
        if ($current_anouncement && date('Y-m-d', strtotime($current_anouncement->end_date)) > date('Y-m-d', strtotime(Carbon::now()))) {
            $total_days = date('d-m-Y', strtotime($current_anouncement->end_date)) -  date('d-m-Y', strtotime(Carbon::now()));
        }


        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.announcements').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/announcementslist', compact('announcements', 'current_anouncement', 'total_days'))->render();
    }

    public function createAnnouncement()
    {
        $mode = 'create';
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('admin.create_announcement').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/announcement-form', compact('mode'))->render();
    }

    public function editAnnouncement($id)
    {
        $mode = 'update';
        $announcement = Announcement::find($id);

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.edit').' '.$announcement->title.' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/announcement-form', compact('announcement', 'mode'))->render();
    }

    public function updateAnnouncement(Request $request, $id)
    {
        $validator = $this->announcementValidator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }

        $announcement = Announcement::find($id);
        $announcement->title = $request->title;
        $announcement->description = $request->description;
        $announcement->start_date = date('Y-m-d', strtotime($request->start_date));
        $announcement->end_date = date('Y-m-d', strtotime($request->end_date));
        $announcement->save();
        $mode = 'update';
        Flash::success(trans('messages.announcement_updated_success'));
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        return $theme->scope('admin/announcement-form', compact('announcement', 'mode'))->render();
    }

    public function addCategory()
    {
        $mode = 'create';
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('admin.create_category').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/category-form', compact('mode'))->render();
    }

    public function editCategory($id)
    {
        $mode = 'update';
        $category = Category::find($id);

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.edit').' '.$category->name.' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/category-form', compact('category', 'mode'))->render();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function categoryValidator(array $data)
    {
        return Validator::make($data, [
            'name'        => 'required|max:30|min:3',
            'description' => 'required',
            'active'      => 'required',
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validator = $this->categoryValidator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }

        $category = Category::create($request->all());
        $category_parent = Category::find($category->id);
        $category_parent->parent_id = $category->id;
        $category_parent->save();

        $categories = Category::paginate(10);

        Flash::success(trans('messages.new_category_added'));

        return redirect('admin/page-settings');
    }

    public function updateCategory(Request $request, $id)
    {
        $validator = $this->categoryValidator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }

        $category = Category::find($id);
        $category_values = $request->only('name', 'description', 'active');
        $category->update($category_values);
        $categories = Category::paginate(10);

        Flash::success(trans('messages.category_updated_success'));

        return redirect('admin/page-settings');
    }

    public function removeCategory(Request $request)
    {
        $category = Category::find($request->category_id);
        if ($category->delete()) {
            Flash::success(trans('messages.category_deleted_success'));

            return response()->json(['status' => '200', 'category' => true, 'message' => 'Category deleted successfully']);
        }
    }

    public function addAnnouncements(Request $request)
    {
        $validator = $this->announcementValidator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }

        $announcements = new Announcement();
        $announcements->title = $request->title;
        $announcements->description = $request->description;
        $announcements->start_date = date('Y-m-d', strtotime($request->start_date));
        $announcements->end_date = date('Y-m-d', strtotime($request->end_date));
        $announcements->save();

        $total_days = '';
        $announcements = Announcement::paginate(10);
        $current_anouncement = Announcement::find(Setting::get('announcement'));
        if ($current_anouncement && date('Y-m-d', strtotime($current_anouncement->end_date)) > date('Y-m-d', strtotime(Carbon::now()))) {
            $total_days = date('d-m-Y', strtotime($current_anouncement->end_date)) -  date('d-m-Y', strtotime(Carbon::now()));
        }
        Flash::success(trans('messages.new_announcement_added'));
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        return $theme->scope('admin/announcementslist', compact('announcements', 'current_anouncement', 'total_days'))->render();
    }

    public function removeAnnouncement(Request $request)
    {
        $announcements = Announcement::find($request->announcement_id);
        if ($announcements->delete()) {
            Flash::success(trans('messages.announcement_deleted_success'));

            return response()->json(['status' => '200', 'announce' => true, 'message' => 'Announcement deleted successfully']);
        }
    }

    public function activeAnnouncement($announcement_id)
    {
        if (Setting::get('announcement') != null) {
            Setting::set('announcement', $announcement_id);
        } else {
            Setting::set('announcement', $announcement_id);
        }

        Flash::success(trans('messages.announcement_activated_success'));

        return redirect()->back();
    }

    public function themes()
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.themes').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        $themes = File::directories(base_path('public/themes'));

        $themesInfo = [];

        //  Setting::get('current_theme')


        foreach ($themes as $key => $value) {
            $themeInfo = json_decode(file_get_contents($value.'/theme.json'));
            $themeInfo->thumbnail = str_replace(base_path('public'), '', $value).'/'.$themeInfo->thumbnail;
            $themeInfo->directory = str_replace(base_path('public/themes/'), '', $value);
            $themesInfo[] = $themeInfo;
        }

        return $theme->scope('admin/themes', compact('themesInfo'))->render();
    }

    public function changeTheme($name)
    {
        Setting::set('current_theme', $name);

        return redirect('admin/themes');
    }

    public function showUsers()
    {
        $users = User::paginate(Setting::get('items_page', 10));

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.manage_users').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/users/show', compact('users'))->render();
    }

    public function editUser($username)
    {
        $timeline = Timeline::where('username', $username)->first();

        if (!$timeline) {
            return redirect('admin/users');
        }

        $user = $timeline->user()->first();

        $user_settings = $user->getUserSettings($user->id);

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.edit').' '.$user->name.' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/users/edit', compact('timeline', 'user', 'username', 'user_settings'))->render();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validateUser(array $data, $timeline_id, $user_id)
    {
        return Validator::make($data, [
            'username' => 'required|max:16|min:5|alpha_num|unique:timelines,username,'.$timeline_id,
            'name'     => 'required',
            'email'    => 'required|unique:users,email,'.$user_id,
        ]);
    }

    public function updateUser($oldUsername, Request $request)
    {
        $data = $request->all();
        $timeline = Timeline::where('username', $oldUsername)->first();
        $user = $timeline->user;

        $validator = $this->validateUser($data, $timeline->id, $user->id);
        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }


        $timeline->update([
                            'username' => $data['username'],
                            'name'     => $data['name'],
                            'about'    => $data['about'],
                            ]);


        $user_details = $request->except('username', 'name', 'about');

        $user_details['birthday'] = date('Y-m-d', strtotime($request->birthday));
        $user->update($user_details);

        $user_settings = [
            'confirm_follow'        => $data['confirm_follow'],
            'comment_privacy'       => $data['comment_privacy'],
            'follow_privacy'        => $data['follow_privacy'],
            'post_privacy'          => $data['post_privacy'],
            'timeline_post_privacy' => $data['timeline_post_privacy'],
            ];

        $users = DB::table('user_settings')->where('user_id', $user->id)
        ->update($user_settings);

        $user_settings = $user->getUserSettings($user->id);

        $username = $timeline->username;

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        Flash::success(trans('messages.user_updated_success'));

        return redirect('admin/users/'.$username.'/edit');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validatePassword(array $data)
    {
        return Validator::make($data, [
            'password'              => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
        ]);
    }

    public function updatePassword(Request $request, $username)
    {
        $validator = $this->validatePassword($request->all());
        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }

        $timeline = Timeline::where('username', $username)->first();
        $user = User::where('timeline_id', $timeline->id)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        $user_settings = $user->getUserSettings($user->id);

        Flash::success(trans('messages.password_updated_success'));
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        return $theme->scope('admin/users/edit', compact('timeline', 'user', 'username', 'user_settings'))->render();
    }

    public function deleteUser($user_id)
    {
        $user = User::find($user_id);
        $chk_user = $user->getUserSettings($user_id);
        if ($chk_user) {
            $chk_delete = $user->deleteUserSettings($user_id);
            if ($chk_delete) {
                if ($user->delete()) {
                    Flash::success(trans('messages.user_deleted_success'));

                    return redirect()->back();
                }
            }
        }
    }

    public function showPages()
    {
        $pages = Page::paginate(Setting::get('items_page', 10));

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.manage_pages').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/pages/show', compact('pages'))->render();
    }

    public function editPage($username)
    {
        $timeline = Timeline::where('username', $username)->first();
        $page = $timeline->page()->first();
        $category_options = ['' => 'Select Category'] + Category::lists('name', 'id')->all();


        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.edit').' '.$timeline->name.' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/pages/edit', compact('category_options', 'username', 'page', 'timeline'))->render();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function adminPageValidation(array $data)
    {
        return Validator::make($data, [
            'name'        => 'required|max:30|min:5',
            'category_id' => 'required',
        ]);
    }

    public function updatePage(Request $request, $username)
    {
        $validator = $this->adminPageValidation($request->all());
        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }

        $timeline = Timeline::where('username', $username)->first();
        $page = Page::where('timeline_id', $timeline->id)->first();

        $timeline->name = $request->name;
        $timeline->about = $request->about;
        $timeline->save();

        $page_details = $request->except('name', 'about', 'username');
        $page->update($page_details);

        $category_options = ['' => 'Select Category'] + Category::lists('name', 'id')->all();
        Flash::success(trans('messages.page_updated_success'));
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        return $theme->scope('admin/pages/edit', compact('category_options', 'username', 'page', 'timeline'))->render();
    }

    public function deletePage($page_id)
    {
        $page = Page::find($page_id);
        if ($page->delete()) {
            Flash::success(trans('messages.page_deleted_success'));

            return redirect()->back();
        }
    }

    public function showGroups()
    {
        $groups = Group::paginate(Setting::get('items_page', 10));

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.manage_groups').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));


        return $theme->scope('admin/groups/show', compact('groups'))->render();
    }

    public function editGroup($username)
    {
        $timeline = Timeline::where('username', $username)->first();
        $groups = $timeline->groups()->first();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.edit').' '.$timeline->name.' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));


        return $theme->scope('admin/groups/edit', compact('timeline', 'groups', 'username'))->render();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function adminGroupValidator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required',
            'type' => 'required',
        ]);
    }

    public function updateGroup(Request $request, $username)
    {
        $validator = $this->adminGroupValidator($request->all());
        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }

        $timeline = Timeline::where('username', $username)->first();
        $groups = Group::where('timeline_id', $timeline->id)->first();
        $groups->type = $request->type;
        $groups->member_privacy = $request->member_privacy;
        $groups->post_privacy = $request->post_privacy;
        $groups->save();

        $timeline->name = $request->name;
        $timeline->about = $request->about;
        $timeline->save();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        Flash::success(trans('messages.group_updated_success'));

        return $theme->scope('admin/groups/edit', compact('timeline', 'groups', 'username'))->render();
    }

    public function deleteGroup($group_id)
    {
        $groups = Group::find($group_id);
        if ($groups->delete()) {
            Flash::success(trans('messages.group_deleted_success'));

            return redirect()->back();
        }
    }

    public function manageReports()
    {
        $user = User::all();
        $post = new Post();
        $page_reports = [];
        $group_reports = [];
        $user_reports = [];

        $post_reports = DB::table('post_reports')->get();
        $timeline_reports = DB::table('timeline_reports')->get();

        foreach ($timeline_reports as $timeline_report) {
            $timeline = Timeline::find($timeline_report->timeline_id);
            if ($timeline != null) {
                if ($timeline->type == 'page') {
                    array_push($page_reports, $timeline_report);
                } elseif ($timeline->type == 'group') {
                    array_push($group_reports, $timeline_report);
                } elseif ($timeline->type == 'user') {
                    array_push($user_reports, $timeline_report);
                }
            }
        }

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.manage_reports').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));


        return $theme->scope('admin/manage-reports', compact('user', 'post_reports', 'post', 'page_reports', 'group_reports', 'user_reports', 'timeline'))->render();
    }

    public function markSafeReports($report_id)
    {
        $post = new Post();
        $check_report = $post->deleteManageReport($report_id);
        if ($check_report) {
            Flash::success(trans('messages.report_mark_safe'));

            return redirect()->back();
        }
    }

    public function deletePostReports($report_id, $post_id)
    {
        $post = Post::find($post_id);
        $notifications = Notification::where('post_id', $post_id)->get();
        //$comments = Comment::where('post_id',$post_id)->get();

        $check_report = $post->deleteManageReport($report_id);
        if ($check_report) {
            if ($notifications != null) {
                foreach ($notifications as $notification) {
                    $notification->delete();
                }
            }


            if ($post->delete()) {
                Flash::success(trans('messages.report_deleted_success'));

                return redirect()->back();
            }
        }
    }

    public function manageAds()
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.manage_ads').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/manage-ads')->render();
    }

    public function updateManageAds(Request $request)
    {
        $settings = $request->except('_token');
        Setting::set($settings);
        Flash::success(trans('messages.ads_updated_success'));

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        return $theme->scope('admin/manage-ads')->render();
    }

    public function settings()
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/settings')->render();
    }

    public function markPageSafeReports($report_id)
    {
        $post = new Post();
        $check_report = $post->deletePageReport($report_id);
        if ($check_report) {
            Flash::success(trans('messages.report_mark_safe'));

            return redirect()->back();
        }
    }

    public function deletePageReports($report_id, $timeline_id)
    {
        $post = new Post();
        $chk_report = $post->deletePageReport($report_id);
        if ($chk_report) {
            $page = Page::where('timeline_id', $timeline_id)->first();
            if ($page) {
                $page->delete();
            }

            $timeline = Timeline::where('id', $timeline_id)->first();
            if ($timeline) {
                $timeline->delete();
            }

            Flash::success(trans('messages.page_deleted_success'));

            return redirect()->back();
        }
    }

    public function deleteGroupReports($report_id, $timeline_id)
    {
        $post = new Post();
        $chk_report = $post->deletePageReport($report_id);
        if ($chk_report) {
            $group = Group::where('timeline_id', $timeline_id)->first();
            if ($group) {
                $group->delete();
            }

            $timeline = Timeline::where('id', $timeline_id)->first();
            if ($timeline) {
                $timeline->delete();
            }

            Flash::success(trans('messages.group_deleted_success'));

            return redirect()->back();
        }
    }

    public function deleteUserReports($report_id, $timeline_id)
    {
        $post = new Post();
        $chk_report = $post->deletePageReport($report_id);
        if ($chk_report) {
            $user = User::where('timeline_id', $timeline_id)->first();
            if ($user) {
                $user->delete();
            }

            // $timeline = Timeline::where('id',$timeline_id)->first();
            //     if($timeline) $timeline->delete();


            Flash::success(trans('messages.user_deleted_success'));

            return redirect()->back();
        }
    }

    public function getEnv()
    {
        if (Config::get('app.env') == 'demo') {
            $env = File::get(base_path('env.example'));
        } else {
            $env = File::get(base_path('.env'));
        }

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');
        $theme->setTitle(trans('common.environment_settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('admin/env', compact('env'))->render();
    }

    public function saveEnv(Request $request)
    {
        Flash::success(trans('common.saved_changes'));

        $env = $request->env;
        file_put_contents(base_path('.env'), $env);

        return redirect('admin/get-env');
    }

    public function getUpdateDatabase(Request $request)
    {
        $migrations = DB::table('migrations')->select('migration')->get();


        $files = array_map('basename', File::allFiles(base_path('database/migrations')));
        $count = 0;
        if (count($migrations) < count($files)) {
            $count = count($files) - count($migrations);
        }


        Artisan::call('migrate:status');
        $output = Artisan::output();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('admin');

        return $theme->scope('admin/update-database', compact('output', 'count'))->render();
    }

    public function postUpdateDatabase(Request $request)
    {
        try {
            Artisan::call('migrate', [
                    '--force' => true,
                ]);
            Artisan::call('view:clear');
        } catch (Exception $e) {
        }

        $output = Artisan::output();
        Flash::success('Update has been done successfully');

        return redirect('admin/update-database');
    }
}
