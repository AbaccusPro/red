<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['post_id', 'user_id', 'timeline_id', 'notified_by', 'description', 'seen', 'type', 'link'];

    public function notified_from()
    {
        return $this->belongsTo('App\User', 'notified_by', 'id');
    }

    public function unread()
    {
        return $this->where('seen', 0);
    }

    /**
     * Get the status of the notification.
     *
     * @param string $value
     *
     * @return string
     */
    public function getSeenAttribute($value)
    {
        return ($value == 0) ? false : true;
    }
}
