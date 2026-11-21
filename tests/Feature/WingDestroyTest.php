<?php

use App\User;

it('the owner deletes the wing', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);

    $this->actingAs($user)->delete('/wings/'.$wing->id)->assertRedirect('/wings');

    $this->assertDatabaseMissing('wings', ['id' => $wing->id]);

    $this->deleteWingImage($wing->image_file);
});

it('another user cannot delete the wing', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $wing = $this->wingOwnedBy($owner);

    $this->actingAs($other)->delete('/wings/'.$wing->id)->assertRedirect();

    $this->assertDatabaseHas('wings', ['id' => $wing->id]);

    // The guard is a compound one: with `or` instead of `and` a signed in stranger would pass it
    // and delete the wing, which the row assertion above catches. The message is asserted where a
    // user sees it, on the page the redirect lands on.
    $this->get('/')->assertSee('You cannot delete it.');

    $this->deleteWingImage($wing->image_file);
});

it('a guest cannot delete the wing', function () {
    $owner = User::factory()->create();
    $wing = $this->wingOwnedBy($owner);

    // The resource routes sit behind the auth middleware, so a guest never reaches destroy() and
    // the Auth::user() half of its guard is unreachable from here. This pins the middleware.
    $this->delete('/wings/'.$wing->id)->assertRedirect();

    $this->assertDatabaseHas('wings', ['id' => $wing->id]);

    $this->deleteWingImage($wing->image_file);
});

it('deleting an unknown wing is not found', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/wings/999999')->assertStatus(404);
});
