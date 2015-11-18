<?php

namespace app;

use Illuminate\Database\Eloquent\Model;

class Wing extends Model
{
    protected $fillable = [
        'name',
        'user_id' // temporary!!
    ];

    /**
     * A wing is owned by an user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * A wing was liked by many users (many to many)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedBy()
    {
        return $this->belongsToMany('App\User', 'users_likes_wings');
    }
}
