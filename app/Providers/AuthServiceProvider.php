<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // The framework registers the policies itself from Laravel 10 on, and this application
        // defines none: there is no App\Model and no App\Policies\ModelPolicy.
    }
}
