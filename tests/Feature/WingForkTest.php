<?php

use App\User;
use App\Wing;

it('a logged in user forks a wing that is not theirs', function () {
    $owner = User::factory()->create();
    $forker = User::factory()->create();
    $original = $this->wingOwnedBy($owner);

    $response = $this->actingAs($forker)->get('/wings/'.$original->id.'/forkit');

    $response->assertStatus(302);

    $fork = Wing::where('id', '!=', $original->id)->first();

    $this->assertNotNull($fork);
    $response->assertRedirect('/wings/'.$fork->id.'/edit');
    $this->assertSame('Test Wing Alpha a forked version', $fork->name);
    $this->assertEquals($forker->id, $fork->user_id);
    $this->assertSame('test-wing-alpha-a-forked-version', $fork->slug);
    $this->assertSame('original image', file_get_contents(storage_path('app'.$fork->image_file)));

    $this->deleteWingImage($original->image_file);
    $this->deleteWingImage($fork->image_file);
});

it('the owner cannot fork a wing of their own', function () {
    $owner = User::factory()->create();
    $original = $this->wingOwnedBy($owner);

    $response = $this->actingAs($owner)->get('/wings/'.$original->id.'/forkit');

    $response->assertStatus(302);
    $response->assertSessionHas('flash_notification');
    $this->assertSame(1, Wing::count());
    $this->get('/')->assertSee('You need to be logged in and');

    $this->deleteWingImage($original->image_file);
});

it('a guest cannot fork a wing', function () {
    $original = $this->wingOwnedBy(User::factory()->create());

    $response = $this->get('/wings/'.$original->id.'/forkit');

    $response->assertStatus(302);
    $response->assertSessionHas('flash_notification');
    $this->assertSame(1, Wing::count());
    $this->get('/')->assertSee('You need to be logged in and');

    $this->deleteWingImage($original->image_file);
});
