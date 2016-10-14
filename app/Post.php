<?php

namespace App;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

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
  protected $fillable = ['timeline_id', 'description', 'user_id', 'youtube_title', 'youtube_video_id', 'location', 'soundcloud_id', 'soundcloud_title'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function users_liked()
    {
        return $this->belongsToMany('App\User', 'post_likes', 'post_id', 'user_id');
    }

    public function shares()
    {
        return $this->belongsToMany('App\User', 'post_shares', 'post_id', 'user_id');
    }

    public function notifications_user()
    {
        return $this->belongsToMany('App\User', 'post_follows', 'post_id', 'user_id');
    }

    public function reports()
    {
        return $this->belongsToMany('App\User', 'post_reports', 'post_id', 'reporter_id')->withPivot('status');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment')->latest()->where('parent_id', null);
    }

    public function users_shared()
    {
        return $this->belongsToMany('App\User', 'post_shares', 'post_id', 'user_id');
    }

    public function images()
    {
        return $this->belongsToMany('App\Media', 'post_media', 'post_id', 'media_id');
    }

    public function users_posts()
    {
        return $this->belongsToMany('App\User', 'posts', 'id', 'user_id');
    }

    public function managePostReport($post_id, $user_id)
    {
        $post_report = DB::table('post_reports')->insert(['post_id' => $post_id, 'reporter_id' => $user_id, 'status' => 'pending', 'created_at' => Carbon::now()]);

        $result = $post_report ? true : false;

        return $result;
    }

    public function check_reports($post_id)
    {
        $post_report = DB::table('post_reports')->where('post_id', $post_id)->first();

        $result = $post_report ? true : false;

        return $result;
    }

    public function deleteManageReport($id)
    {
        $post_report = DB::table('post_reports')->where('id', $id)->delete();

        $result = $post_report ? true : false;

        return $result;
    }

    public function getUserName($id)
    {
        $user = User::find($id);
        $timeline = Timeline::where('id', $user->timeline_id)->first();
        $result = $timeline ? $timeline->username : false;

        return $result;
    }

    public function getAvatar($id)
    {
        $user = User::find($id);
        $timeline = Timeline::where('id', $user->timeline_id)->first();
        $media = Media::where('id', $timeline->avatar_id)->first();

        $result = $media ? $media->source : false;

        return $result;
    }

    public function getGender($id)
    {
        $user = User::find($id);

        $result = $user ? $user->gender : false;

        return $result;
    }

    public function postsLiked()
    {
        $result = DB::table('post_likes')->get();

        return $result;
    }

    public function postsReported()
    {
        $result = DB::table('post_reports')->get();

        return $result;
    }

    public function postShared()
    {
        $result = DB::table('post_shares')->get();

        return $result;
    }

    public function chkUserFollower($login_id, $post_user_id)
    {
        $followers = DB::table('followers')->where('follower_id', $post_user_id)->where('leader_id', $login_id)->where('status', '=', 'approved')->first();

        if ($followers) {
            $userSettings = DB::table('user_settings')->where('user_id', $login_id)->first();
            $result = $userSettings ? $userSettings->comment_privacy : false;

            return $result;
        }
    }

    public function chkUserSettings($login_id)
    {
        $userSettings = DB::table('user_settings')->where('user_id', $login_id)->first();
        $result = $userSettings ? $userSettings->comment_privacy : false;

        return $result;
    }

    public function users_tagged()
    {
        return $this->belongsToMany('App\User', 'post_tags', 'post_id', 'user_id');
    }

    public function getPageName($id)
    {
        $timeline = Timeline::where('id', $id)->first();
        $result = $timeline ? $timeline->username : false;

        return $result;
    }

    public function deletePageReport($id)
    {
        $timeline_report = DB::table('timeline_reports')->where('id', $id)->delete();
        $result = $timeline_report ? true : false;

        return $result;
    }
}
