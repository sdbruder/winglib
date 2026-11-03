<?php

use App\User;
it('the gravatar url is stored when the address has one', function () {
    Gravatar::shouldReceive('exists')->once()->andReturn(true);
    Gravatar::shouldReceive('get')->once()->andReturn('https://example.test/avatar.png');

    $user = User::factory()->create();

    $this->assertSame('https://example.test/avatar.png', $user->avatar);
});

it('the default avatar is stored when the address has no gravatar', function () {
    Gravatar::shouldReceive('exists')->once()->andReturn(false);

    $user = User::factory()->create();

    $this->assertSame('/img/default-avatar.jpg', $user->avatar);
});

it('an avatar already set is kept', function () {
    Gravatar::shouldReceive('exists')->never();

    $user = User::factory()->create(['avatar' => 'https://example.test/kept.png']);

    $this->assertSame('https://example.test/kept.png', $user->avatar);
});
