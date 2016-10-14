<?php

use Cmgmyr\Messenger\Models\Message;
use Intervention\Image\Facades\Image;

/*
|--------------------------------------------------------------------------
| API routes
|--------------------------------------------------------------------------
*/

Route::get('/contact', 'PageController@contact');
Route::post('/contact', 'PageController@saveContact');
Route::get('/share-post/{id}', 'PageController@sharePost');
Route::get('/get-location/{location}', 'HomeController@getLocation');

Route::group(['prefix' => 'api', 'middleware' => ['auth', 'cors'], 'namespace' => 'API'], function () {
    Route::group(['prefix' => 'v1'], function () {
        require config('infyom.laravel_generator.path.api_routes');
    });
});

Route::post('pusher/auth', function (Illuminate\Http\Request $request, Pusher $pusher) {
    return $pusher->presence_auth(
        $request->input('channel_name'),
        $request->input('socket_id'),
        uniqid(),
        ['username' => $request->input('username')]
    );
});


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::resource('timelines', 'TimelineController');

Route::group(['middleware' => ['web']], function () {
    Route::auth();
});


// Redirect to facebook to authenticate
Route::get('facebook', 'Auth\AuthController@facebookRedirect');
// Get back to redirect url
Route::get('account/facebook', 'Auth\AuthController@facebook');

// Redirect to google to authenticate
Route::get('google', 'Auth\AuthController@googleRedirect');
// Get back to redirect url
Route::get('account/google', 'Auth\AuthController@google');

// Redirect to twitter to authenticate
Route::get('twitter', 'Auth\AuthController@twitterRedirect');
// Get back to redirect url
Route::get('account/twitter', 'Auth\AuthController@twitter');

// Redirect to linkedin to authenticate
Route::get('linkedin', 'Auth\AuthController@linkedinRedirect');
// Get back to redirect url
Route::get('account/linkedin', 'Auth\AuthController@linkedin');


// Login
Route::get('/login', 'Auth\AuthController@getLogin');
Route::post('/login', 'Auth\AuthController@login');

// Register
Route::get('/register', 'Auth\AuthController@register');

Route::post('/register', 'Auth\AuthController@registerUser');

Route::get('email/verify', 'Auth\AuthController@verifyEmail');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', 'TimelineController@showFeed');
    Route::get('/browse', 'TimelineController@showGlobalFeed');
});


Route::get('/home', 'HomeController@index');

Route::post('/member/update-role', 'TimelineController@assignMemberRole');
Route::post('/member/updatepage-role', 'TimelineController@assignPageMemberRole');
Route::get('/post/{post_id}', 'TimelineController@singlePost');
/*
|--------------------------------------------------------------------------
| Admin routes
|--------------------------------------------------------------------------
*/


Route::group(['prefix' => '/admin', 'middleware' => ['auth', 'role:admin']], function () {
    Route::get('/', 'AdminController@dashboard');
    Route::get('/general-settings', 'AdminController@generalSettings');
    Route::post('/general-settings', 'AdminController@updateGeneralSettings');

    Route::get('/user-settings', 'AdminController@userSettings');
    Route::post('/user-settings', 'AdminController@updateUserSettings');

    Route::get('/page-settings', 'AdminController@pageSettings');
    Route::post('/page-settings', 'AdminController@updatePageSettings');

    Route::get('/group-settings', 'AdminController@groupSettings');
    Route::post('/group-settings', 'AdminController@updateGroupSettings');

    Route::get('/custom-pages', 'AdminController@listCustomPages');
    Route::get('/custom-pages/create', 'AdminController@createCustomPage');
    Route::post('/custom-pages', 'AdminController@storeCustomPage');
    Route::get('/custom-pages/{id}/edit', 'AdminController@editCustomPage');
    Route::post('/custom-pages/{id}/update', 'AdminController@updateCustomPage');

    Route::get('/announcements', 'AdminController@getAnnouncements');
    Route::get('/announcements/create', 'AdminController@createAnnouncement');
    Route::get('/announcements/{id}/edit', 'AdminController@editAnnouncement');
    Route::post('/announcements/{id}/update', 'AdminController@updateAnnouncement');
    Route::post('/announcements', 'AdminController@addAnnouncements');
    Route::get('/activate/{announcement_id}', 'AdminController@activeAnnouncement');

    Route::get('/themes', 'AdminController@themes');
    Route::get('/change-theme/{name}', 'AdminController@changeTheme');

    Route::get('/users', 'AdminController@showUsers');
    Route::get('/users/{username}/edit', 'AdminController@editUser');
    Route::post('/users/{username}/edit', 'AdminController@updateUser');
    Route::get('/users/{user_id}/delete', 'AdminController@deleteUser');

    Route::get('/users/{username}/delete', 'AdminController@deleteUser');
    Route::post('/users/{username}/newpassword', 'AdminController@updatePassword');

    Route::get('/pages', 'AdminController@showPages');
    Route::get('/pages/{username}/edit', 'AdminController@editPage');
    Route::post('/pages/{username}/edit', 'AdminController@updatePage');
    Route::get('/pages/{page_id}/delete', 'AdminController@deletePage');


    Route::get('/groups', 'AdminController@showGroups');
    Route::get('/groups/{username}/edit', 'AdminController@editGroup');
    Route::post('/groups/{username}/edit', 'AdminController@updateGroup');
    Route::get('/groups/{group_id}/delete', 'AdminController@deleteGroup');


    Route::get('/manage-reports', 'AdminController@manageReports');
    Route::post('/manage-reports', 'AdminController@updateManageReports');
    Route::get('/mark-safe/{report_id}', 'AdminController@markSafeReports');
    Route::get('/delete-post/{report_id}/{post_id}', 'AdminController@deletePostReports');

    Route::get('/manage-ads', 'AdminController@manageAds');
    Route::get('/update-database', 'AdminController@getUpdateDatabase');
    Route::post('/update-database', 'AdminController@postUpdateDatabase');
    Route::get('/get-env', 'AdminController@getEnv');
    Route::post('/save-env', 'AdminController@saveEnv');
    Route::post('/manage-ads', 'AdminController@updateManageAds');
    Route::get('/settings', 'AdminController@settings');
    Route::get('/markpage-safe/{report_id}', 'AdminController@markPageSafeReports');
    Route::get('/deletepage-post/{report_id}/{timeline_id}', 'AdminController@deletePageReports');
    Route::get('/deleteuser-post/{report_id}/{timeline_id}', 'AdminController@deleteUserReports');
    Route::get('/deletegroup-post/{report_id}/{timeline_id}', 'AdminController@deleteGroupReports');

    Route::get('/category/create', 'AdminController@addCategory');
    Route::post('/category/create', 'AdminController@storeCategory');
    Route::get('/category/{id}/edit', 'AdminController@editCategory');
    Route::post('/category/{id}/update', 'AdminController@updateCategory');
});


/*
|--------------------------------------------------------------------------
| Messages routes
|--------------------------------------------------------------------------
*/

    Route::get('messages/{username?}', 'MessageController@index');


/*
|--------------------------------------------------------------------------
| User routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '/{username}', 'middleware' => 'auth'], function ($username) {
    Route::get('/', 'TimelineController@showTimeline');

    Route::get('/messages', 'UserController@messages');

    Route::get('/followers', 'UserController@followers');

    Route::get('/following', 'UserController@following');

    Route::get('/posts', 'TimelineController@posts');

    Route::get('/liked-pages', 'UserController@likedPages');
    Route::get('/joined-groups', 'UserController@joinedGroups');
    Route::get('/follow-requests', 'UserController@followRequests');

    Route::get('/create-page', 'TimelineController@addPage');
    Route::post('/create-page', 'TimelineController@createPage');

    Route::get('/create-group', 'TimelineController@addGroup');
    Route::post('/create-group', 'TimelineController@createGroupPage');
    Route::get('/members/{group_id}', 'TimelineController@getGroupMember');

    Route::get('/pages-groups', 'TimelineController@pagesGroups');

    Route::get('/groupadmin/{group_id}', 'TimelineController@getAdminMember');
    Route::get('/groupposts/{group_id}', 'TimelineController@getGroupPosts');
    Route::get('/page-posts', 'TimelineController@getPagePosts');
    Route::get('/add-members', 'UserController@membersList');
    Route::get('/page-likes', 'TimelineController@getPageLikes');
    Route::get('/pagemembers', 'TimelineController@getPageMember');
    Route::get('/pageadmin', 'TimelineController@getPageAdmins');
    Route::get('/add-pagemembers', 'UserController@pageMembersList');

    Route::get('/notification/{id}', 'NotificationController@redirectNotification');
});



Route::group(['prefix' => '/{username}/settings', 'middleware' => ['auth', 'editown']], function ($username) {
    Route::get('/general', 'UserController@userGeneralSettings');
    Route::post('/general', 'UserController@saveUserGeneralSettings');

    Route::get('/privacy', 'UserController@userPrivacySettings');

    Route::post('/privacy', 'UserController@SaveUserPrivacySettings');

    Route::get('/password', 'UserController@userPasswordSettings');
    Route::post('/password', 'UserController@saveNewPassword');

    Route::get('/affliates', 'UserController@affliates');

    Route::get('/deactivate', 'UserController@deactivate');
    Route::get('/deleteme', 'UserController@deleteMe');

    Route::get('/notifications', 'UserController@emailNotifications');
    Route::post('/notifications', 'UserController@updateEmailNotifications');
});

/*
|--------------------------------------------------------------------------
| User dashboard routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => '/{username}/page-settings', 'middleware' => ['auth', 'editpage']], function ($username) {
    Route::get('/general', 'TimelineController@generalPageSettings');
    Route::post('/general', 'TimelineController@updateGeneralPageSettings');
    Route::get('/privacy', 'TimelineController@privacyPageSettings');
    Route::post('/privacy', 'TimelineController@updatePrivacyPageSettings');
    Route::get('/roles', 'TimelineController@rolesPageSettings');
    Route::get('/likes', 'TimelineController@likesPageSettings');
});

Route::group(['prefix' => '/{username}/group-settings', 'middleware' => ['auth', 'editgroup']], function ($username) {
    Route::get('/general', 'TimelineController@userGroupSettings');
    Route::post('/general', 'TimelineController@updateUserGroupSettings');
    Route::get('/closegroup', 'TimelineController@userGroupSettings');
    Route::get('/join-requests/{group_id}', 'TimelineController@getJoinRequests');
});

/*
|--------------------------------------------------------------------------
| Ajax Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for ajax.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => 'ajax', 'middleware' => ['auth']], function () {
    Route::post('create-post', 'TimelineController@createPost');

    Route::post('get-youtube-video', 'TimelineController@getYoutubeVideo');
    Route::post('like-post', 'TimelineController@likePost');
    Route::post('follow-post', 'TimelineController@follow');
    Route::post('notify-user', 'TimelineController@getNotifications');
    Route::post('post-comment', 'TimelineController@postComment');
    Route::post('page-like', 'TimelineController@pageLike');
    Route::post('change-avatar', 'TimelineController@changeAvatar');
    Route::post('change-cover', 'TimelineController@changeCover');
    Route::post('comment-like', 'TimelineController@likeComment');
    Route::post('comment-delete', 'TimelineController@deleteComment');
    Route::post('post-delete', 'TimelineController@deletePost');
    Route::post('page-delete', 'TimelineController@deletePage');
    Route::post('share-post', 'TimelineController@sharePost');
    Route::post('page-liked', 'TimelineController@pageLiked');
    Route::post('get-soundcloud-results', 'TimelineController@getSoundCloudResults');
    Route::post('join-group', 'TimelineController@joiningGroup');
    Route::post('join-close-group', 'TimelineController@joiningClosedGroup');
    Route::post('join-accept', 'TimelineController@acceptJoinRequest');
    Route::post('join-reject', 'TimelineController@rejectJoinRequest');
    Route::post('follow-accept', 'UserController@acceptFollowRequest');
    Route::post('follow-reject', 'UserController@rejectFollowRequest');
    Route::get('get-more-posts', 'TimelineController@getMorePosts');
    Route::get('get-more-feed', 'TimelineController@showFeed');
    Route::get('get-global-feed', 'TimelineController@showGlobalFeed');
    Route::post('add-memberGroup', 'UserController@addingMembersGroup');
    Route::post('get-users', 'UserController@getUsersJoin');
    Route::get('get-users-mentions', 'UserController@getUsersMentions');
    Route::post('groupmember-remove', 'TimelineController@removeGroupMember');
    Route::post('group-join', 'TimelineController@timelineGroups');
    Route::post('report-post', 'TimelineController@reportPost');
    Route::post('follow-user-confirm', 'TimelineController@userFollowRequest');
    Route::post('post-message/{id}', 'MessageController@update');
    Route::post('create-message', 'MessageController@store');
    Route::post('page-report', 'TimelineController@pageReport');
    Route::post('get-notifications', 'UserController@getNotifications');
    Route::post('get-unread-notifications', 'UserController@getUnreadNotifications');
    Route::post('get-messages', 'MessageController@getMessages');
    Route::post('get-message/{id}', 'MessageController@getMessage');
    Route::post('get-conversation/{id}', 'MessageController@show');
    Route::post('get-private-conversation/{userId}', 'MessageController@getPrivateConversation');
    Route::post('get-unread-message', 'UserController@getUnreadMessage');
    Route::post('get-unread-messages', 'MessageController@getUnreadMessages');
    Route::post('pagemember-remove', 'TimelineController@removePageMember');
    Route::post('get-users-modal', 'UserController@getUsersModal');
    Route::post('edit-post', 'TimelineController@editPost');
    Route::get('load-emoji', 'TimelineController@loadEmoji');
    Route::post('update-post', 'TimelineController@updatePost');
    Route::post('/mark-all-notifications', 'NotificationController@markAllRead');
    Route::post('add-page-members', 'UserController@addingMembersPage');
    Route::post('get-members-join', 'UserController@getMembersJoin');
    Route::post('announce-delete', 'AdminController@removeAnnouncement');
    Route::post('category-delete', 'AdminController@removeCategory');
});




/*
|--------------------------------------------------------------------------
| Image routes
|--------------------------------------------------------------------------
*/

Route::get('user/avatar/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/users/avatars/'.$filename)->response();
});

Route::get('user/cover/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/users/covers/'.$filename)->response();
});

Route::get('user/gallery/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/users/gallery/'.$filename)->response();
});

Route::get('page/avatar/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/pages/avatars/'.$filename)->response();
});

Route::get('page/cover/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/pages/covers/'.$filename)->response();
});

Route::get('group/avatar/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/groups/avatars/'.$filename)->response();
});

Route::get('group/cover/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/groups/covers/'.$filename)->response();
});

Route::get('setting/{filename}', function ($filename) {
    return Image::make(storage_path().'/uploads/settings/'.$filename)->response();
});

Route::get('/page/{pagename}', 'PageController@page');
