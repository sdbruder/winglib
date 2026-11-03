<?php

namespace Tests\Unit;

use App\User;
use Gravatar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_gravatar_url_is_stored_when_the_address_has_one()
    {
        Gravatar::shouldReceive('exists')->once()->andReturn(true);
        Gravatar::shouldReceive('get')->once()->andReturn('https://example.test/avatar.png');

        $user = factory(User::class)->create();

        $this->assertSame('https://example.test/avatar.png', $user->avatar);
    }

    public function test_the_default_avatar_is_stored_when_the_address_has_no_gravatar()
    {
        Gravatar::shouldReceive('exists')->once()->andReturn(false);

        $user = factory(User::class)->create();

        $this->assertSame('/img/default-avatar.jpg', $user->avatar);
    }

    public function test_an_avatar_already_set_is_kept()
    {
        Gravatar::shouldReceive('exists')->never();

        $user = factory(User::class)->create(['avatar' => 'https://example.test/kept.png']);

        $this->assertSame('https://example.test/kept.png', $user->avatar);
    }
}
