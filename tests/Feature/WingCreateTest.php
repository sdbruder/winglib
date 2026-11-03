<?php

use App\User;
use App\Wing;

it('the create form asks for a drawn image', function () {
    $response = $this->get('/wings/create');

    $response->assertStatus(200);
    $response->assertSee('image_base64');
});

it('a guest cannot store a wing', function () {
    $response = $this->post('/wings', [
        'name' => 'Test Wing Alpha',
        'image_base64' => $this->base64Png('abcdefgh'),
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('flash_notification');
    $this->assertDatabaseMissing('wings', ['name' => 'Test Wing Alpha']);

    $this->get('/')->assertSee('You cant save a wing without a user.');
});

it('a user stores a wing with its image and panels', function () {
    $user = User::factory()->create();
    $bytes = 'abcdefgh';

    $response = $this->actingAs($user)->post('/wings', array_merge([
        'name' => 'Test Wing Alpha',
        'description' => 'A wing made by a test',
        'unitsystem' => 'metric',
        'unitValue' => 'metric',
        'image_base64' => $this->base64Png($bytes),
    ], $this->panelFields()));

    $response->assertStatus(302);
    $response->assertRedirect('/wings');

    $stored = Wing::where('name', 'Test Wing Alpha')->first();

    $this->assertNotNull($stored);
    $this->assertSame('test-wing-alpha', $stored->slug);
    $this->assertEquals($user->id, $stored->user_id);
    $this->assertSame($bytes, file_get_contents(storage_path('app'.$stored->image_file)));

    $wing = Wing::find($stored->id);

    $this->assertCount(6, $wing->panels);
    $this->assertEquals(42, $wing->totalspan);
    $this->assertEquals([1, 2, 3, 4, 5, 6], array_column($wing->panels, 'span'));

    $this->deleteWingImage($stored->image_file);
});

it('a name shorter than five characters is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/wings', array_merge([
        'name' => 'Wng',
        'image_base64' => $this->base64Png('abcdefgh'),
    ], $this->panelFields()));

    $response->assertStatus(302);
    $response->assertSessionHasErrors('name');
    $this->assertDatabaseMissing('wings', ['name' => 'Wng']);
});
