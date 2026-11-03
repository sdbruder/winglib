<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_form_is_served()
    {
        $this->get('/auth/login')->assertStatus(200);
    }

    public function test_a_user_logs_in_with_the_right_password()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $response = $this->post('/auth/login', ['email' => $user->email, 'password' => 'secret']);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_password_is_rejected()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $response = $this->post('/auth/login', ['email' => $user->email, 'password' => 'wrong']);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_a_user_logs_out()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/auth/logout');

        $response->assertStatus(302);
        $this->assertGuest();
    }

    public function test_a_user_registers()
    {
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
    }

    public function test_a_mismatched_password_confirmation_is_rejected()
    {
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
    }

    public function test_an_email_that_is_already_taken_is_rejected()
    {
        factory(User::class)->create(['email' => 'test@example.test']);

        $response = $this->post('/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['name' => 'Test User']);
    }

    public function test_a_password_reset_link_is_sent()
    {
        $user = factory(User::class)->create();

        Notification::fake();

        $response = $this->post('/password/email', ['email' => $user->email]);

        $response->assertStatus(302);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_a_user_resets_the_password_through_the_password_resets_table()
    {
        $user = factory(User::class)->create(['password' => bcrypt('old-password')]);

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
    }
}
