<?php

use App\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

it('the login form is served', function () {
    $this->get('/auth/login')->assertStatus(200);
});

it('a user logs in with the right password', function () {
    $user = User::factory()->create(['password' => bcrypt('secret')]);

    $response = $this->post('/auth/login', ['email' => $user->email, 'password' => 'secret']);

    $response->assertStatus(302);
    $response->assertRedirect('/');
    $this->assertAuthenticatedAs($user);
});

it('a wrong password is rejected', function () {
    $user = User::factory()->create(['password' => bcrypt('secret')]);

    $response = $this->post('/auth/login', ['email' => $user->email, 'password' => 'wrong']);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('a user logs out', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/auth/logout');

    $response->assertStatus(302);
    $this->assertGuest();
});

it('a user registers', function () {
    $response = $this->post('/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.test',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect('/');
    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.test')->first();

    $this->assertNotNull($user);
    $this->assertSame('/img/default-avatar.jpg', $user->avatar);
});

it('a mismatched password confirmation is rejected', function () {
    $response = $this->post('/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.test',
        'password' => 'secret123',
        'password_confirmation' => 'different',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('password');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'test@example.test']);
});

it('an email that is already taken is rejected', function () {
    User::factory()->create(['email' => 'test@example.test']);

    $response = $this->post('/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.test',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('email');
    $this->assertDatabaseMissing('users', ['name' => 'Test User']);
});

it('a password reset link is sent', function () {
    $user = User::factory()->create();

    Notification::fake();

    $response = $this->post('/password/email', ['email' => $user->email]);

    $response->assertStatus(302);
    Notification::assertSentTo($user, ResetPassword::class);
});

it('a user resets the password through the password resets table', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password')]);

    DB::table('password_resets')->insert([
        'email' => $user->email,
        'token' => Hash::make('reset-token'),
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    $response = $this->post('/password/reset', [
        'email' => $user->email,
        'token' => 'reset-token',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertStatus(302);
    $this->assertTrue(Hash::check('new-password', User::find($user->id)->password));
    $this->assertSame(0, DB::table('password_resets')->where('email', $user->email)->count());
});
