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

it('redirects a guest json request to the login page', function () {
    // Two behaviours here, and only one of them is a contract worth pinning. The contract is that a
    // guest cannot reach the edit page: 302, never the form. What the redirect targets is not: this
    // app sends the guest to the app root (observed Location "http://localhost"), not to
    // /auth/login, and Handler::unauthenticated()'s expectsJson() branch returning a 401 is
    // unreachable from these routes because the middleware redirects instead of throwing. Both are
    // recorded in winglib-status.md rather than frozen here. The middleware had no coverage at all
    // before this test.
    $wing = $this->createWing(['name' => 'Guest Wing']);

    $this->getJson('/wings/'.$wing->id.'/edit')->assertStatus(302);
});
