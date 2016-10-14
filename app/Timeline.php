<?php

namespace App;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @SWG\Definition(
 *      definition="Timeline",
 *      required={},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="username",
 *          description="username",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="about",
 *          description="about",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="avatar_id",
 *          description="avatar_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="cover_id",
 *          description="cover_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="cover_position",
 *          description="cover_position",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="type",
 *          description="type",
 *          type="string"
 *      )
 * )
 */
class Timeline extends Model
{
    use SoftDeletes;

    public $table = 'timelines';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];


    public $fillable = [
        'username',
        'name',
        'about',
        'avatar_id',
        'cover_id',
        'cover_position',
        'type',
        'deleted_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'             => 'integer',
        'username'       => 'string',
        'name'           => 'string',
        'about'          => 'string',
        'avatar_id'      => 'integer',
        'cover_id'       => 'integer',
        'cover_position' => 'string',
        'type'           => 'string',
        'deleted_at'     => 'datetime',
    ];

    /**
     * Validation rules.
     *
     * @var array
     */
    public static $rules = [

    ];

    public function toArray()
    {
        $array = parent::toArray();

        $cover_url = $this->cover()->get()->toArray();
        $avatar_url = $this->avatar()->get()->toArray();
        $array['cover_url'] = $cover_url;
        $array['avatar_url'] = $avatar_url;

        if ($this->type == 'user') {
            $array['verified'] = $this->user()->first() ? $this->user()->first()->verified : 0;
        } else {
            $array['verified'] = $this->page()->first() ? $this->page()->first()->verified : 0;
        }

        return $array;
    }

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    public function user()
    {
        return $this->hasOne('App\User');
    }

    public function avatar()
    {
        return $this->belongsTo('App\Media', 'avatar_id');
    }

    public function cover()
    {
        return $this->belongsTo('App\Media', 'cover_id');
    }

    public function page()
    {
        return $this->hasOne('App\Page');
    }

    public function groups()
    {
        return $this->hasOne('App\Group');
    }

    public function reports()
    {
        return $this->belongsToMany('App\User', 'timeline_reports', 'timeline_id', 'reporter_id')->withPivot('status');
    }
}
