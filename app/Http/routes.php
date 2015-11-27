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

// AJAX method to a user like a wing
Route::post('wings/likeit', 'WingsController@likeit');

// Wings resource (CRUD, etcetera)
Route::get(     'wing/{slug}', 'WingsController@showSlug');
Route::resource('wings',       'WingsController');

// User settings
Route::get('user/settings', 'UsersController@settings');

// Authentication routes
Route::get( 'auth/login',  'Auth\AuthController@getLogin');
Route::post('auth/login',  'Auth\AuthController@postLogin');
Route::get( 'auth/logout', 'Auth\AuthController@getLogout');
Route::get( 'auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

// Password reset routes
Route::controllers(['password' => 'Auth\PasswordController']);

// OAuth Authentication routes
Route::get('auth/{driver}',          'Auth\AuthController@redirectToProvider');
Route::get('auth/{driver}/callback', 'Auth\AuthController@handleProviderCallback');
