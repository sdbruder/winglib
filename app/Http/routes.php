<?php

//
// Application Routes
//

Route::get('/', function () {
    return view('pages.home');
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
