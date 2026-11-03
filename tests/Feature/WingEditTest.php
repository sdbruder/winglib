<?php

use App\User;
use App\Wing;

it('the owner opens the edit form', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);

    $response = $this->actingAs($user)->get('/wings/'.$wing->id.'/edit');

    $response->assertStatus(200);
    $response->assertViewHas('wing');
    $this->assertEquals($wing->id, $this->viewData($response, 'wing')->id);

    $this->deleteWingImage($wing->image_file);
});

it('another user cannot open the edit form', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $wing = $this->wingOwnedBy($owner);

    $response = $this->actingAs($other)->get('/wings/'.$wing->id.'/edit');

    $response->assertStatus(302);
    $response->assertSessionHas('flash_notification');
    $this->get('/')->assertSee('This wing is not yours or you are not logged in.');

    $this->deleteWingImage($wing->image_file);
});

it('a guest cannot open the edit form', function () {
    $wing = $this->wingOwnedBy(User::factory()->create());

    $response = $this->get('/wings/'.$wing->id.'/edit');

    $response->assertStatus(302);
    $response->assertSessionHas('flash_notification');
    $this->get('/')->assertSee('This wing is not yours or you are not logged in.');

    $this->deleteWingImage($wing->image_file);
});

it('the owner updates the wing and its image', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);
    $bytes = 'ijklmnop';

    $response = $this->actingAs($user)->put('/wings/'.$wing->id, array_merge([
        'name' => 'Test Wing Beta',
        'description' => 'An updated wing',
        'unitsystem' => 'metric',
        'image_base64' => $this->base64Png($bytes),
    ], $this->panelFields()));

    $response->assertStatus(302);
    $response->assertRedirect('/wings');

    $updated = Wing::find($wing->id);

    $this->assertSame('Test Wing Beta', $updated->name);
    $this->assertSame($bytes, file_get_contents(storage_path('app'.$updated->image_file)));

    $this->deleteWingImage($updated->image_file);
});
