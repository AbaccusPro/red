<?php

namespace App\Http\Controllers;

use App\Group;
use App\Http\Requests;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Notification;
use App\Page;
use App\Repositories\UserRepository;
use App\Role;
use App\Setting;
use App\Timeline;
use App\User;
use Auth;
use Flash;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;
use Teepluss\Theme\Facades\Theme;
use Validator;

class UserController extends AppBaseController
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepository = $userRepo;
        $this->middleware('disabledemo', ['only' => 'deleteMe']);
    }

    /**
     * Display a listing of the User.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $this->userRepository->pushCriteria(new RequestCriteria($request));
        $users = $this->userRepository->all();

        return view('users.index')
            ->with('users', $users);
    }

    /**
     * Show the form for creating a new User.
     *
     * @return Response
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created User in storage.
     *
     * @param CreateUserRequest $request
     *
     * @return Response
     */
    public function store(CreateUserRequest $request)
    {
        $input = $request->all();

        $user = $this->userRepository->create($input);

        Flash::success('User saved successfully.');

        return redirect(route('users.index'));
    }

    /**
     * Display the specified User.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $user = $this->userRepository->findWithoutFail($id);

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        return view('users.show')->with('user', $user);
    }

    /**
     * Show the form for editing the specified User.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $user = $this->userRepository->findWithoutFail($id);

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        return view('users.edit')->with('user', $user);
    }

    /**
     * Update the specified User in storage.
     *
     * @param int               $id
     * @param UpdateUserRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateUserRequest $request)
    {
        $user = $this->userRepository->findWithoutFail($id);

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        $user = $this->userRepository->update($request->all(), $id);

        Flash::success('User updated successfully.');

        return redirect(route('users.index'));
    }

    /**
     * Remove the specified User from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $user = $this->userRepository->findWithoutFail($id);

        if (empty($user)) {
            Flash::error('User not found');

            return redirect(route('users.index'));
        }

        $this->userRepository->delete($id);

        Flash::success('User deleted successfully.');

        return redirect(route('users.index'));
    }

    public function userGeneralSettings($username)
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.general_settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/settings/general', compact('username'))->render();
    }

    public function userPrivacySettings($username)
    {
        $timeline = Timeline::where('username', $username)->first();

        if ($timeline == null) {
            return Redirect::to('/');
        }

        $settings = DB::table('user_settings')->where('user_id', $timeline->user->id)->first();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.privacy_settings').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/settings/privacy', compact('settings'))->render();
    }

    public function userPasswordSettings($username)
    {
        $timeline = Timeline::where('username', $username)
        ->get()->toArray();

        if ($timeline == null) {
            return Redirect::to('/');
        }

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');

        return $theme->scope('users/settings/password')->render();
    }

    public function affliates($username)
    {
        $referrals = User::where('affiliate_id', Auth::user()->id)->where('id', '!=', Auth::user()->id)->paginate(Setting::get('items_page', 10));

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.affiliates').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/affliates', compact('referrals'))->render();
    }

    public function deactivate($username)
    {
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.deactivate_account').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/deactivate')->render();
    }

    public function deleteMe($username)
    {
        Auth::user()->timeline()->delete();
        $user = Auth::user()->delete();

        return redirect('/');
    }

    public function emailNotifications($username)
    {
        $timeline = Timeline::where('username', $username)->with('user')->first();
        $user = $timeline->user;
        $userSettings = $user->getUserSettings($user->id);

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.email_notifications').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/notifications', compact('userSettings'))->render();
    }

    public function updateEmailNotifications($username, Request $request)
    {
        $timeline = Timeline::where('username', $username)->with('user')->first();
        $user = $timeline->user;
        $input = $request->except('_token');

        $user_settings = [
                            'email_follow'        => $input['email_follow'],
                            'email_like_post'     => $input['email_like_post'],
                            'email_post_share'    => $input['email_post_share'],
                            'email_comment_post'  => $input['email_comment_post'],
                            'email_like_comment'  => $input['email_like_comment'],
                            'email_reply_comment' => $input['email_reply_comment'],
                            'email_join_group'    => $input['email_join_group'],
                            'email_like_page'     => $input['email_like_page'], ];

        $privacy = DB::table('user_settings')->where('user_id', $user->id)
                   ->update($user_settings);

        Flash::success(trans('messages.email_notifications_updated_success'));

        return Redirect::back();
    }

    public function SaveUserPrivacySettings($username, Request $request)
    {
        $timeline = Timeline::where('username', $username)->with('user')->first();
        $user = $timeline->user;
        $input = $request->except('_token');

        $user_settings = [
                            'confirm_follow'        => $input['confirm_follow'],
                            'comment_privacy'       => $input['comment_privacy'],
                            'follow_privacy'        => $input['follow_privacy'],
                            'post_privacy'          => $input['post_privacy'],
                            'timeline_post_privacy' => $input['timeline_post_privacy'], ];

        $privacy = DB::table('user_settings')->where('user_id', $user->id)
                   ->update($user_settings);

        Flash::success(trans('messages.privacy_settings_updated_success'));

        return Redirect::back();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function generalSettingsValidator(array $data)
    {
        return Validator::make($data, [
            'username' => 'required|max:16|min:5|alpha_num|unique:timelines,username,'.Auth::user()->timeline->id,
            'name'     => 'required',
            'email'    => 'unique:users,email,'.Auth::id(),
        ]);
    }

    public function saveUserGeneralSettings(Request $request)
    {
        if (Config::get('app.env') == 'demo' && $request->username == 'bootstrapguru') {
            Flash::error(trans('common.disabled_on_demo'));

            return Redirect::back();
        }

        $data = $request->all();
        $data['username'] = $request->new_username;
        $validator = $this->generalSettingsValidator($data);
        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }
        $user = User::find(Auth::user()->id);
        $timeline = Timeline::find($user->timeline_id);
        $timeline->update([
                            'username' => $data['username'],
                            'name'     => $data['name'],
                            'about'    => $data['about'],
                            ]);

        $user_details = $request->except('username', 'name', 'about');
        $user_details['birthday'] = date('Y-m-d', strtotime($request->birthday));
        $user->update($user_details);

        Flash::success(trans('messages.general_settings_updated_success'));

        return redirect()->back();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function passwordValidator(array $data)
    {
        return Validator::make($data, [
            'new_password'     => 'required|min:6',
            'current_password' => 'required|min:6',
        ]);
    }

    public function saveNewPassword(Request $request)
    {
        if (Config::get('app.env') == 'demo' && $request->username == 'bootstrapguru') {
            Flash::error(trans('common.disabled_on_demo'));

            return Redirect::back();
        }

        $validator = $this->passwordValidator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
            ->withInput($request->all())
            ->withErrors($validator->errors());
        }

        $user = User::findOrFail(Auth::user()->id);
        if (Hash::check($request->current_password, Auth::user()->password)) {
            if ($request->current_password != $request->new_password) {
                $user->password = bcrypt($request->new_password);
                $user->save();
                Flash::success(trans('messages.new_password_updated_success'));

                return redirect()->back();
            } else {
                Flash::error(trans('messages.password_no_match'));

                return redirect()->back()->with('Password not match.');
            }
        } else {
            Flash::error(trans('messages.old_password_no_match'));

            return redirect()->back()->with('Password not match.');
        }
    }

    public function messages($username)
    {
        $timeline = Timeline::where('username', $username)->with('user')->first();
        $user = $timeline->user;
        $trending_tags = Hashtag::orderBy('count', 'desc')->get()->random(Setting::get('min_items_page', 5));
        $following = $user->following()->get();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.messages').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/messages', compact('following'))->render();
    }

    public function membersList($username)
    {
        $timeline = Timeline::where('username', $username)->with('groups')->first();
        $group = $timeline->groups;
        $group_members = $group->members();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.add_members').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/addmembers', compact('timeline', 'group', 'group_members'))->render();
    }

    public function pageMembersList($username)
    {
        $timeline = Timeline::where('username', $username)->with('page')->first();
        $page = $timeline->page;
        $page_members = $page->members();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.add_members').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/addpagemembers', compact('timeline', 'page', 'page_members'))->render();
    }

    public function getUsersJoin(Request $request)
    {
        $timelines = Timeline::where('username', 'like', "%{$request->searchname}%")->where('type', 'user')->where('username', '!=', Auth::user()->username)->get();
        $group_id = $request->group_id;
        $group = Group::findOrFail($request->group_id);

        $users = new \Illuminate\Database\Eloquent\Collection();


        foreach ($timelines as $key => $timeline) {
            $user = $timeline->user()->with(['groups' => function ($query) use ($group_id) {
                $query->where('groups.id', $group_id);
            }])->get();

            $users->add($user);
        }

        return response()->json(['status' => '200', 'data' => $users]);
    }

    public function getMembersJoin(Request $request)
    {
        $timelines = Timeline::where('username', 'like', "%{$request->searchname}%")->where('type', 'user')->where('username', '!=', Auth::user()->username)->get();
        $page_id = $request->page_id;
        $page = Page::find($request->page_id);

        $users = new \Illuminate\Database\Eloquent\Collection();


        foreach ($timelines as $key => $timeline) {
            $user = $timeline->user()->with(['pages' => function ($query) use ($page_id) {
                $query->where('pages.id', $page_id);
            }])->get();

            $users->add($user);
        }

        return response()->json(['status' => '200', 'data' => $users]);
    }

    public function getUsersMentions(Request $request)
    {
        $requestData = $request->all();
        $timelines = Timeline::where('name', 'like', "%{$requestData['query']}%")->orWhere('username', 'like', "%{$requestData['query']}%")->where('type', 'user')->get();

        $users = $timelines;
        foreach ($timelines as $key => $value) {
            if ($value->avatar != null) {
                $users[$key]['image'] = url('user/avatar/'.$value->avatar->source);
            } else {
                $gender = isset($value->user) ? $value->user->gender : 'male';
                $users[$key]['image'] = url('user/avatar/default-'.$gender.'-avatar.png');
            }
        }

        return response()->json($users);
    }

    public function addingMembersGroup(Request $request)
    {
        $group = Group::findOrFail($request->group_id);

        if ($request->user_status == 'Joined') {
            $group->users()->detach([$request->user_id]);

            return response()->json(['status' => '200', 'added' => true, 'message' => 'successfully unjoined']);
        } else {
            $chkUser = $group->chkGroupUser($request->group_id, $request->user_id);

            if ($chkUser) {
                $group_user = $group->updateStatus($chkUser->id);
                if ($group_user) {
                    return response()->json(['status' => '200', 'added' => true, 'message' => 'successfully accepted']);
                }
            } else {
                $user_role = Role::where('name', '=', 'user')->first();
                $group->users()->attach($request->user_id, ['group_id' => $request->group_id, 'role_id' => $user_role->id, 'status' => 'approved']);

                return response()->json(['status' => '200', 'added' => true, 'message' => 'successfully added']);
            }
        }
    }

    // public function addingMembersPage(Request $request)
    // {
    //     $page = Page::findOrFail($request->group_id);

    //         if ($request->user_status == "Joined")
    //         {
    //             $page->users()->detach([$request->user_id]);
    //             return response()->json(['status' => '200','added' => true,'message'=>'successfully unjoined']);
    //         }
    //         else
    //         {
    //             $chkUser = $page->chkPageUser($request->group_id,$request->user_id);

    //             if($chkUser)
    //             {
    //                 $page_user = $page->updateStatus($chkUser->id);
    //                 if($page_user)
    //                 return response()->json(['status' => '200','added' => true,'message'=>'successfully accepted']);
    //             }
    //             else
    //             {
    //               $user_role = Role::where('name','=','user')->first();
    //               $page->users()->attach($request->user_id, array('page_id'=>$request->page_id,'role_id'=>$user_role->id,'status'=>'approved'));
    //               return response()->json(['status' => '200','added' => true,'message'=>'successfully added']);
    //             }

    //         }
    // }

    public function addingMembersPage(Request $request)
    {
        $page = Page::find($request->page_id);

        if ($request->user_status == 'Joined') {
            $page->users()->detach([$request->user_id]);

            return response()->json(['status' => '200', 'added' => true, 'message' => 'successfully unjoined']);
        } else {
            $page->users()->attach($request->user_id, ['page_id' => $request->page_id, 'role_id' => 2, 'active' => 1]);

            return response()->json(['status' => '200', 'added' => true, 'message' => 'successfully added']);
        }
    }

    public function followers($username)
    {
        $timeline = Timeline::where('username', $username)->with('user', 'user.pageLikes', 'user.groups', 'user.followers')->first();
        $user = $timeline->user;
        $joined_groups_count = $user->groups()->where('role_id', '!=', 1)->where('status', '=', 'approved')->get()->count();
        $followers = $user->followers()->where('status', '=', 'approved')->get();
        $followRequests = $user->followers()->where('status', '=', 'pending')->get();
        $following_count = $user->following()->where('status', '=', 'approved')->get()->count();
        $followers_count = $user->followers()->where('status', '=', 'approved')->get()->count();
        $follow_user_status = '';
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
        $own_pages = $user->own_pages();
        $own_groups = $user->own_groups();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.followers').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/followers', compact('timeline', 'user', 'followers', 'followRequests', 'own_groups', 'own_pages', 'follow_user_status', 'following_count', 'followers_count', 'follow_confirm', 'user_post', 'joined_groups_count'))->render();
    }

    public function following($username)
    {
        $timeline = Timeline::where('username', $username)->with('user', 'user.pageLikes', 'user.groups')->first();
        $user = $timeline->user;
        $following = $user->following()->where('status', '=', 'approved')->get();
        $followRequests = $user->followers()->where('status', '=', 'pending')->get();
        $following_count = $user->following()->where('status', '=', 'approved')->get()->count();
        $followers_count = $user->followers()->where('status', '=', 'approved')->get()->count();
        $joined_groups_count = $user->groups()->where('role_id', '!=', 1)->where('status', '=', 'approved')->get()->count();
        $follow_user_status = '';
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
        $own_pages = $user->own_pages();
        $own_groups = $user->own_groups();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.following').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/following', compact('timeline', 'user', 'following', 'followRequests', 'follow_user_status', 'following_count', 'followers_count', 'follow_confirm', 'user_post', 'joined_groups_count', 'own_pages', 'own_groups'))->render();
    }

    public function likedPages($username)
    {
        $timeline = Timeline::where('username', $username)->with('user', 'user.pageLikes', 'user.groups')->first();
        $user = $timeline->user;
        $liked_pages = $user->pageLikes;
        $joined_groups_count = $user->groups()->where('role_id', '!=', 1)->where('status', '=', 'approved')->get()->count();
        $following_count = $user->following()->where('status', '=', 'approved')->get()->count();
        $followers_count = $user->followers()->where('status', '=', 'approved')->get()->count();
        $followRequests = $user->followers()->where('status', '=', 'pending')->get();
        $follow_user_status = '';
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
        $own_pages = $user->own_pages();
        $own_groups = $user->own_groups();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.liked_pages').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/liked-pages', compact('timeline', 'liked_pages', 'user', 'followRequests', 'own_pages', 'own_groups', 'follow_user_status', 'following_count', 'followers_count', 'follow_confirm', 'user_post', 'joined_groups_count'))->render();
    }

    public function joinedGroups($username)
    {
        $timeline = Timeline::where('username', $username)->with('user', 'user.pageLikes', 'user.groups')->first();
        $user = $timeline->user;
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $joined_groups = $user->groups()->where('role_id', '!=', $admin_role_id->id)->where('status', '=', 'approved')->get();
        $joined_groups_count = $joined_groups->count();
        $following_count = $user->following()->where('status', '=', 'approved')->get()->count();
        $followers_count = $user->followers()->where('status', '=', 'approved')->get()->count();
        $followRequests = $user->followers()->where('status', '=', 'pending')->get();
        $follow_user_status = '';
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
        $own_pages = $user->own_pages();
        $own_groups = $user->own_groups();

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.joined_groups').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/joined-groups', compact('timeline', 'user', 'joined_groups', 'followRequests', 'own_groups', 'own_pages', 'follow_user_status', 'following_count', 'followers_count', 'follow_confirm', 'user_post', 'joined_groups_count'))->render();
    }

    public function followRequests($username)
    {
        $timeline = Timeline::where('username', $username)->with('user', 'user.pageLikes', 'user.groups')->first();
        $user = $timeline->user;
        $followRequests = $user->followers()->where('status', '=', 'pending')->get();
        $joined_groups_count = $user->groups()->where('role_id', '!=', 1)->where('status', '=', 'approved')->get()->count();
        $following_count = $user->following()->where('status', '=', 'approved')->get()->count();
        $followers_count = $user->followers()->where('status', '=', 'approved')->get()->count();
        $follow_user_status = '';
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
        $own_pages = $user->own_pages();
        $own_groups = $user->own_groups();


        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $theme->setTitle(trans('common.follow_requests').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users/follow-requests', compact('timeline', 'user', 'followRequests', 'own_groups', 'own_pages',
            'follow_user_status', 'following_count', 'followers_count', 'follow_confirm', 'user_post', 'joined_groups_count'))->render();
    }

    public function acceptFollowRequest(Request $request)
    {
        $user = User::find($request->user_id);

        $follow_user = $user->updateFollowStatus($request->user_id);

        if ($follow_user) {
            Flash::success(trans('messages.request_accepted'));
        }

        //Notify the user for accepting the follow request
        Notification::create(['user_id' => $request->user_id, 'timeline_id' => $user->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' accepted your follow request', 'type' => 'accept_follow_request', 'link' => Auth::user()->username.'/followers']);

        return response()->json(['status' => '200', 'accepted' => true, 'message' => 'follow request successfully accepted']);
    }

    public function rejectFollowRequest(Request $request)
    {
        $user = User::find($request->user_id);


        $follow_user = $user->decilneRequest($request->user_id);

        if ($follow_user) {
            Flash::success(trans('messages.request_rejected'));
        }

        //Notify the user for rejecting the follow request
        Notification::create(['user_id' => $request->user_id, 'timeline_id' => $user->timeline_id, 'notified_by' => Auth::user()->id, 'description' => Auth::user()->name.' rejected your follow request', 'type' => 'reject_follow_request', 'link' => Auth::user()->username]);

        return response()->json(['status' => '200', 'rejected' => true, 'message' => 'follow request successfully accepted']);
    }

    public function getNotifications()
    {
        $notifications = Notification::where('user_id', Auth::user()->id)->with('notified_from')->latest()->paginate(Setting::get('items_page', 10));

        return response()->json(['status' => '200', 'notifications' => $notifications]);
    }

    public function getUnreadNotifications()
    {
        $notifications = Notification::where('seen', 0)->where('user_id', Auth::user()->id)->count();

        return response()->json(['status' => '200', 'unread_notifications' => $notifications]);
    }

    public function getUsersModal(Request $request)
    {
        $users = User::whereIn('id', explode(',', $request->user_ids))->get();
        $heading = isset($request->heading) ? $request->heading : 'Users';

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');
        $responseHtml = $theme->partial('modal', ['users' => $users, 'heading' => $heading]);

        return response()->json(['status' => '200', 'responseHtml' => $responseHtml]);
    }
}
