<?php

namespace App;

use Auth;
use Cmgmyr\Messenger\Traits\Messagable;
use DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable
{
    use SoftDeletes, EntrustUserTrait {

        SoftDeletes::restore insteadof EntrustUserTrait;
        EntrustUserTrait::restore insteadof SoftDeletes;

    }
    use Messagable;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


      /**
       * The attributes that are mass assignable.
       *
       * @var array
       */
      protected $appends = [
        'name',
        'avatar',
        'cover',
        'about',
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'timeline_id', 'email', 'verification_code', 'email_verified', 'remember_token', 'password', 'birthday', 'city', 'gender', 'last_logged', 'timezone', 'affiliate_id', 'language', 'country', 'active', 'verified', 'facebook_link', 'twitter_link', 'dribbble_link', 'instagram_link', 'youtube_link', 'linkedin_link',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'verification_code', 'email', 'timeline',
    ];

    /**
     * Get the user's  name.
     *
     * @param string $value
     *
     * @return string
     */
    public function getNameAttribute($value)
    {
        return $this->timeline->name;
    }

    /**
     * Get the user's  username.
     *
     * @param string $value
     *
     * @return string
     */
    public function getUsernameAttribute($value)
    {
        return $this->timeline->username;
    }

    /**
     * Get the user's  avatar.user().
     *
     * @param string $value
     *
     * @return string
     */
    public function getAvatarAttribute($value)
    {
        return $this->timeline->avatar ? url('user/avatar/'.$this->timeline->avatar->source) : url('user/avatar/default-'.$this->gender.'-avatar.png');
    }

    /**
     * Get the user's  cover.
     *
     * @param string $value
     *
     * @return string
     */
    public function getCoverAttribute($value)
    {
        return $this->timeline->cover ? $this->timeline->cover->source : null;
    }

    /**
     * Get the user's  about.
     *
     * @param string $value
     *
     * @return string
     */
    public function getAboutAttribute($value)
    {
        return $this->timeline->about ? $this->timeline->about : null;
    }

    public function toArray()
    {
        $array = parent::toArray();

        $timeline = $this->timeline->toArray();

        foreach ($timeline as $key => $value) {
            if ($key != 'id') {
                $array[$key] = $value;
            }
        }

        $array['avatar'] = $this->avatar;

        return $array;
    }

    public function timeline()
    {
        return $this->belongsTo('App\Timeline');
    }

    public function followers()
    {
        return $this->belongsToMany('App\User', 'followers', 'leader_id', 'follower_id')->withPivot('status');
    }

    public function following()
    {
        return $this->belongsToMany('App\User', 'followers', 'follower_id', 'leader_id');
    }

    public function pages()
    {
        return $this->belongsToMany('App\Page', 'page_user', 'user_id', 'page_id')->withPivot('role_id', 'active');
    }

    public function own_pages()
    {
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $own_pages = $this->pages()->where('role_id', $admin_role_id->id)->where('page_user.active', 1)->get();

        $result = $own_pages ? $own_pages : false;

        return $result;
    }

    public function own_groups()
    {
        $admin_role_id = Role::where('name', '=', 'admin')->first();
        $own_groups = $this->groups()->where('role_id', $admin_role_id->id)->where('status', 'approved')->get();

        $result = $own_groups ? $own_groups : false;

        return $result;
    }

    public function groups()
    {
        return $this->belongsToMany('App\Group', 'group_user', 'user_id', 'group_id')->withPivot('role_id', 'status');
    }

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    public function pageLikes()
    {
        return $this->belongsToMany('App\Page', 'page_likes', 'user_id', 'page_id');
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification')->with('notified_from');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Role', 'role_user', 'user_id', 'role_id');
    }

    public function get_group($id)
    {
        return $this->groups()->where('groups.id', $id)->first();
    }

    public function get_page($id)
    {
        return $this->pages()->where('pages.id', $id)->first();
        // $result = $user_page ? $user_page : false;
        // return $result;
    }

    public function getUserSettings($user_id)
    {
        $result = DB::table('user_settings')->where('user_id', $user_id)->first();

        return $result;
    }

    public function deleteUserSettings($user_id)
    {
        $result = DB::table('user_settings')->where('user_id', $user_id)->delete();

        return $result;
    }

    public function getOthersSettings($username)
    {
        $timeline = Timeline::where('username', $username)->first();
        $user = self::where('timeline_id', $timeline->id)->first();
        $result = DB::table('user_settings')->where('user_id', $user->id)->first();

        return $result;
    }

    public function getReportsCount()
    {
        $post_reports = DB::table('post_reports')->get();
        $timeline_reports = DB::table('timeline_reports')->get();
        $result1 = count($post_reports);
        $result2 = count($timeline_reports);

        return $result1 + $result2;
    }

    public function updateFollowStatus($user_id)
    {
        $chk_user = DB::table('followers')->where('follower_id', $user_id)->where('leader_id', Auth::user()->id)->first();
        if ($chk_user->status == 'pending') {
            $result = DB::table('followers')->where('follower_id', $user_id)->where('leader_id', Auth::user()->id)->update(['status' => 'approved']);
        }

        $result = $result ? true : false;

        return $result;
    }

    public function decilneRequest($user_id)
    {
        $chk_user = DB::table('followers')->where('follower_id', $user_id)->where('leader_id', Auth::user()->id)->first();
        if ($chk_user->status == 'pending') {
            $result = DB::table('followers')->where('follower_id', $user_id)->where('leader_id', Auth::user()->id)->delete();
        }

        $result = $result ? true : false;

        return $result;
    }

    public function announcements()
    {
        return $this->belongsToMany('App\Announcement', 'announcement_user', 'user_id', 'announcement_id');
    }

    public function chkMyFollower($diff_timeline_id, $login_id)
    {
        $followers = DB::table('followers')->where('follower_id', $diff_timeline_id)->where('leader_id', $login_id)->where('status', '=', 'approved')->first();
        $result = $followers ? true : false;

        return $result;
    }

    public function conversations()
    {
        return $this->belongsToMany('App\Conversation', 'conversation_user', 'user_id', 'conversation_id');
    }

    public function messages()
    {
        return $this->conversations()->with('messages');
    }

    public function getUserPrivacySettings($loginId, $others_id)
    {
        $timeline_post_privacy = '';
        $timeline_post = '';
        $user_post = '';
        $result = '';

        $live_user_settings = $this->getUserSettings($others_id);

        if ($live_user_settings) {
            $timeline_post_privacy = $live_user_settings->timeline_post_privacy;
            $user_post_privacy = $live_user_settings->post_privacy;
        }

        //start $this if block is for timeline post privacy settings
           if ($loginId != $others_id) {
               if ($timeline_post_privacy != null && $timeline_post_privacy == 'only_follow') {
                   $isFollower = $this->chkMyFollower($others_id, $loginId);
                   if ($isFollower) {
                       $timeline_post = true;
                   }
               } elseif ($timeline_post_privacy != null && $timeline_post_privacy == 'everyone') {
                   $timeline_post = true;
               } elseif ($timeline_post_privacy != null && $timeline_post_privacy == 'nobody') {
                   $timeline_post = false;
               }

                //start $this if block is for user post privacy settings
                if ($user_post_privacy != null && $user_post_privacy == 'only_follow') {
                    $isFollower = $this->chkMyFollower($others_id, $loginId);
                    if ($isFollower) {
                        $user_post = true;
                    }
                } elseif ($user_post_privacy != null && $user_post_privacy == 'everyone') {
                    $user_post = true;
                }
           } else {
               $timeline_post = true;
               $user_post = true;
           }
           //End
        $result = $timeline_post.'-'.$user_post;

        return $result;
    }
}
