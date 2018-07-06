<?php

//
// Application Routes
//

Use App\Wing;

Route::get('/', function () {
    $last = Wing::orderBy('updated_at', 'desc')->take(5)->get();
    $rand = Wing::orderByRaw("RAND()")->take(5)->get();
    $voted = Wing::leftJoin('users_likes_wings', 'users_likes_wings.wing_id', '=', 'wings.id')
        ->select(DB::raw('wings.*, count(users_likes_wings.wing_id) as likes'))      //'wings.*', 'count(users_likes_wings.wing_id) as likes')
        ->groupBy('wings.id')
        ->orderBy('likes', 'desc')
        ->take(5)->get();
    return view('pages.home', [
        'last' => $last,
        'rand' => $rand,
        'voted' => $voted]);
});

// Wings stuff
Route::get(     'wing/{slug}',       'WingsController@showSlug'); // Show by slug
Route::post(    'wings/likeit',      'WingsController@likeit'  ); // AJAX to like a wing
Route::get(     'wings/{id}/forkit', 'WingsController@forkIt'  ); // forkit
Route::resource('wings',             'WingsController'         ); // Wings resource

// User settings
Route::get('user/settings', 'UsersController@settings');

// Authentication Routes...
Route::get( 'auth/login',    'Auth\LoginController@showLoginForm')->name('login');
Route::post('auth/login',    'Auth\LoginController@login');
Route::get('auth/logout',    'Auth\LoginController@logout')->name('logout');
Route::post('auth/logout',   'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::get( 'auth/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('auth/register', 'Auth\RegisterController@register');

// Password Reset Routes...
Route::get( 'password/reset',         'Auth\ForgotPasswordController@showLinkRequestForm');
Route::post('password/email',         'Auth\ForgotPasswordController@sendResetLinkEmail');
Route::get( 'password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
Route::post('password/reset',         'Auth\ResetPasswordController@reset');

// OAuth Authentication routes
Route::get('auth/{driver}',          'Auth\LoginController@redirectToProvider'    );
Route::get('auth/{driver}/callback', 'Auth\LoginController@handleProviderCallback');

