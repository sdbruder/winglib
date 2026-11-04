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

it('keeps the thumbnails when a wing cannot be drawn', function () {
    // refreshImageVariants() swallows a drawing failure so a save that cannot produce a thumbnail
    // still succeeds; that catch is lines 259-260 and no test reached it. A wing whose panels have
    // no span is the case: the drawing refuses it and the thumbnails must stay as they were.
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);
    $path = storage_path('app'.$wing->imageVariant(180));
    $before = File::get($path);

    foreach (range(1, 6) as $panel) {
        foreach (['span', 'chord', 'sweep', 'angle'] as $field) {
            $wing->{"panel{$panel}{$field}"} = 0;
        }
    }

    $wing->save();

    expect(File::get($path))->toBe($before);

    $this->deleteWingImage($wing->image_file);
});

it('writes the two widths the views ask for, by their names', function () {
    // home.blade.php asks for the 180 thumbnail and index.blade.php for the 282 one, while the
    // other tests here read Wing::IMAGE_VARIANTS and so move with a change to it. A mutation that
    // drops or shifts an entry moved the writer and the reader together and stayed invisible;
    // naming the files pins the contract the views depend on.
    $user = User::factory()->create();

    $this->actingAs($user)->post('/wings', array_merge([
        'name' => 'Named Variant Wing',
        'description' => 'a wing whose thumbnails are named',
        'unitsystem' => 'metric',
        'image_base64' => $this->base64Png('canvas bytes'),
    ], $this->panelFields()));

    $wing = Wing::query()->where('name', 'Named Variant Wing')->firstOrFail();
    $base = preg_replace('/\.png$/', '', $wing->image_file);

    foreach (['180', '282'] as $width) {
        $path = storage_path('app'.$base.'-'.$width.'.png');

        expect(File::exists($path))->toBeTrue("the $width thumbnail was not written; looked for $path");
        expect(getimagesize($path)[0])->toBe((int) $width);
    }

    $this->deleteWingImage($wing->image_file);
});

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
