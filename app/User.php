<?php

namespace App;

use Gravatar;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable, HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'avatar',
        'provider',
        'provider_id',
        'token',
        'token_secret',
        'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'token_secret'];

    /**
     * boot method
     */
    protected static function boot()
    {
        parent::boot();

        // When saving user, make sure:
        // - provider, provider_id and token fields àre filled in
        static::saving(function (User $user) {
            if (empty($user->provider)) {
                $user->provider = 'email';
                $user->provider_id = $user->email;
            }
            if (empty($user->token)) {
                $user->token = bcrypt($user->provider . '::' . $user->provider_id);
            }

            if (($user->email) && empty($user->avatar)) {
                if (Gravatar::exists($user->email)) {
                    $user->avatar = Gravatar::get($user->email);
                } else {
                    $user->avatar = '/img/default-avatar.jpg';
                }
            }

            return true;
        });
    }

    /**
     * A user can have many wings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function wings()
    {
        return $this->hasMany('App\Wing');
    }

    /**
     * A user likes many wings (many to many)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likes()
    {
        return $this->belongsToMany('App\Wing', 'users_likes_wings');
    }
}
