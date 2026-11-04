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
