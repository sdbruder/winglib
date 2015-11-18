<?php

namespace app\Providers;

use App\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // When saving user, make sure:
        // - provider, provider_id and token fields re filled in
        User::saving(function (User $user) {
            if (empty($user->provider)) {
                $user->provider = 'email';
                $user->provider_id = $user->email;
            }
            if (empty($user->token)) {
                $user->token = bcrypt($user->provider . '::' . $user->provider_id);
            }
            return true;
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
