<?php

use App\User;

it('serves the user settings page', function () {
    // app/Http/Controllers/UsersController.php sat at 0% coverage: the route exists and the route
    // table test says so, but nothing had ever rendered the page.
    $user = User::factory()->create();

    $this->actingAs($user)->get('/user/settings')
        ->assertOk()
        ->assertSee('User settings');
});

it('serves the password reset request form', function () {
    // Found while verifying the beta deploy: GET /password/reset answered 500 with
    // "View [auth.passwords.email] not found", because Laravel's SendsPasswordResetEmails trait
    // renders that name while this app's template is auth/password.blade.php. The page had no test,
    // which is why nothing caught it - and line coverage could not either, since the view() call
    // lives in the vendor trait.
    $this->get('/password/reset')
        ->assertOk()
        ->assertSee('Password reset')
        ->assertSee('password/email', false)
        ->assertSee('name="email"', false);
});

it('sends a guest away from the edit page, json client included', function () {
    // These routes do not use the auth middleware: every wing action guards itself with
    // Auth::user(), which is why the framework's JSON handling never engages here and why
    // Handler::unauthenticated() stayed unreached. It is wired into Handler::render() now, so a
    // 401 is one route-group change away; today a guest gets the controller's redirect whether the
    // client asked for JSON or for HTML.
    $wing = $this->createWing(['name' => 'Guest Wing']);

    $this->getJson('/wings/'.$wing->id.'/edit')->assertStatus(302);
    $this->get('/wings/'.$wing->id.'/edit')->assertStatus(302);
});

it('sends a signed in user away from the login page', function () {
    // The guest alias is App\Http\Middleware\RedirectIfAuthenticated, which carried one uncovered
    // mutant: dropping its early return would show the login form to someone already signed in.
    $user = User::factory()->create();

    $this->actingAs($user)->get('/auth/login')->assertRedirect('/');
});
