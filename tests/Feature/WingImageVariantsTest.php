<?php

use App\User;
use App\Wing;
use Illuminate\Support\Facades\File;

function variantPaths(Wing $wing): array
{
    $paths = [];

    foreach (Wing::IMAGE_VARIANTS as $width) {
        $paths[$width] = storage_path('app'.$wing->imageVariant($width));
    }

    return $paths;
}

it('writes the thumbnails when a wing is created', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/wings', array_merge([
        'name' => 'Variant Wing',
        'description' => 'a wing that needs thumbnails',
        'unitsystem' => 'metric',
        'image_base64' => $this->base64Png('canvas bytes'),
    ], $this->panelFields()));

    $wing = Wing::query()->where('name', 'Variant Wing')->firstOrFail();

    expect(File::exists(storage_path('app'.$wing->image_file)))->toBeTrue();

    foreach (variantPaths($wing) as $width => $path) {
        expect(File::exists($path))->toBeTrue("the $width thumbnail was not written; looked for $path");

        $size = getimagesize($path);

        expect($size[0])->toBe($width);
    }

    $this->deleteWingImage($wing->image_file);
});

it('writes the thumbnails when a wing is forked', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $wing = $this->wingOwnedBy($owner);

    $this->actingAs($other)->get('/wings/'.$wing->id.'/forkit');

    $fork = Wing::query()->where('id', '!=', $wing->id)->latest('id')->firstOrFail();

    expect($fork->image_file)->not->toBe($wing->image_file);

    foreach (variantPaths($fork) as $width => $path) {
        expect(File::exists($path))->toBeTrue("the $width thumbnail was not written for the fork");
    }

    $this->deleteWingImage($fork->image_file);
    $this->deleteWingImage($wing->image_file);
});

it('redraws the thumbnails when a wing changes', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);

    // the model writes them on save in the first place
    $this->actingAs($user)->get('/wings/'.$wing->id.'/edit')->assertStatus(200);

    $before = file_get_contents(storage_path('app'.$wing->imageVariant(180)));

    $this->actingAs($user)->put('/wings/'.$wing->id, array_merge([
        'name' => 'Test Wing Alpha',
        'description' => 'a different planform',
        'unitsystem' => 'metric',
        'image_base64' => $this->base64Png('other canvas bytes'),
    ], $this->panelFields(), ['panel1span' => 800]));

    $after = file_get_contents(storage_path('app'.$wing->imageVariant(180)));

    expect($after)->not->toBe($before);

    $this->deleteWingImage($wing->image_file);
});

it('refreshes the thumbnails when the full size image is redrawn', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);

    foreach (variantPaths($wing) as $path) {
        File::delete($path);
    }

    $this->artisan('wings:draw', ['wing' => $wing->id, '--force' => true])->assertExitCode(0);

    foreach (variantPaths($wing) as $width => $path) {
        expect(File::exists($path))->toBeTrue("the $width thumbnail was not refreshed");
    }

    $this->deleteWingImage($wing->image_file);
});
