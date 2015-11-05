<?php

namespace App;

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
    public function user() {
        return $this->belongsTo('App\User');
    }
}
