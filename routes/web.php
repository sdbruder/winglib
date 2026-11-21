<?php

//
// Application Routes
//

Route::get('/', 'HomeController@index'); // Index Home Page

// Wings stuff
Route::get('wing/{slug}', 'WingsController@showSlug'); // Show by slug
Route::post('wings/likeit', 'WingsController@likeit'); // AJAX to like a wing
Route::get('wings/{id}/forkit', 'WingsController@forkIt'); // forkit
Route::resource('wings', 'WingsController'); // Wings resource

// User settings
Route::get('user/settings', 'UsersController@settings');

// Authentication Routes...
Route::get('auth/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('auth/login', 'Auth\LoginController@login');
Route::get('auth/logout', 'Auth\LoginController@logout')->name('logout');
Route::post('auth/logout', 'Auth\LoginController@logout');

// Registration Routes...
Route::get('auth/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('auth/register', 'Auth\RegisterController@register');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

// OAuth Authentication routes
Route::get('auth/{driver}', 'Auth\LoginController@redirectToProvider');
Route::get('auth/{driver}/callback', 'Auth\LoginController@handleProviderCallback');
