<?php

use App\User;
use Laravel\Socialite\Facades\Socialite;

it('an unknown driver is not found', function () {
    $this->get('/auth/nope')->assertStatus(404);
});

it('the social drivers redirect to their provider', function () {
    $hosts = ['facebook' => 'facebook.com', 'google' => 'accounts.google.com'];

    foreach ($hosts as $driver => $host) {
        $response = $this->get('/auth/'.$driver);

        $response->assertStatus(302);

        $location = $response->baseResponse->headers->get('Location');

        $this->assertStringEndsWith($host, parse_url($location, PHP_URL_HOST));
        $this->assertSame(
            config('services.'.$driver.'.redirect'),
            $this->queryValue($location, 'redirect_uri')
        );
    }
});

it('the configured redirects are absolute callbacks', function () {
    foreach (['facebook', 'google'] as $driver) {
        $redirect = config('services.'.$driver.'.redirect');

        $this->assertStringStartsWith('http', $redirect);
        $this->assertStringEndsWith('/auth/'.$driver.'/callback', $redirect);
    }
});

/** A driver mock whose user() returns what a provider callback would have fetched. */
function socialiteDriver($user)
{
    $driver = Mockery::mock();
    $driver->shouldReceive('user')->once()->andReturn($user);

    return $driver;
}

it('creates a user from a provider callback and logs them in', function () {
    $socialite = new Laravel\Socialite\Two\User;
    $socialite->id = 'fb-1';
    $socialite->name = 'Social User';
    $socialite->nickname = 'socialuser';
    $socialite->email = 'social@example.test';
    $socialite->avatar = 'https://example.test/avatar.png';
    $socialite->token = 'facebook-token';

    Socialite::shouldReceive('driver')->with('facebook')->andReturn(socialiteDriver($socialite));

    $this->get('/auth/facebook/callback')->assertRedirect('/');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'provider' => 'facebook',
        'provider_id' => 'fb-1',
        'username' => 'socialuser',
        'email' => 'social@example.test',
        'token' => 'facebook-token',
        'token_secret' => null,
    ]);
});

it('logs in the user a callback returns again instead of a second row', function () {
    $user = User::factory()->create([
        'provider' => 'facebook',
        'provider_id' => 'fb-9',
        'token' => 'existing-facebook-token',
    ]);

    $socialite = new Laravel\Socialite\Two\User;
    $socialite->id = 'fb-9';
    $socialite->name = $user->name;
    $socialite->nickname = 'socialuser';
    $socialite->email = $user->email;
    $socialite->avatar = 'https://example.test/avatar.png';
    $socialite->token = 'existing-facebook-token';

    Socialite::shouldReceive('driver')->with('facebook')->andReturn(socialiteDriver($socialite));

    $this->get('/auth/facebook/callback')->assertRedirect('/');

    // The users table has a unique index on provider and provider_id, so a second insert would
    // fail rather than duplicate; this pins the branch that looks the user up first.
    expect(User::query()->where('provider_id', 'fb-9')->count())->toBe(1);
    $this->assertAuthenticatedAs($user->fresh());
});

it('stores the token secret when the provider sends one', function () {
    // Twitter style users carry a token secret; Facebook and Google do not, which is the branch
    // the other two tests take.
    $socialite = new class
    {
        public $id = 'tw-1';

        public $name = 'Twitter User';

        public $nickname = 'twitteruser';

        public $email = 'twitter@example.test';

        public $avatar = 'https://example.test/twitter.png';

        public $token = 'twitter-token';

        public $tokenSecret = 'twitter-secret';
    };

    Socialite::shouldReceive('driver')->with('twitter')->andReturn(socialiteDriver($socialite));

    $this->get('/auth/twitter/callback')->assertRedirect('/');

    $this->assertDatabaseHas('users', [
        'provider' => 'twitter',
        'provider_id' => 'tw-1',
        'token' => 'twitter-token',
        'token_secret' => 'twitter-secret',
    ]);
});

it('is not found when the provider returns no user', function () {
    Socialite::shouldReceive('driver')->with('facebook')->andReturn(socialiteDriver(null));

    $this->get('/auth/facebook/callback')->assertStatus(404);
});
