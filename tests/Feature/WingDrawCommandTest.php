<?php

use App\User;
use Illuminate\Support\Facades\File;

it('redraws a wing image from its measurements', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);
    $path = storage_path('app'.$wing->image_file);

    unlink($path);

    $this->artisan('wings:draw', ['wing' => $wing->id])->assertExitCode(0);

    expect(File::exists($path))->toBeTrue();

    $size = getimagesize($path);

    expect($size[0])->toBe(940)->and($size[1])->toBe(400);
    expect(filesize($path))->toBeGreaterThan(1000);

    $this->deleteWingImage($wing->image_file);
});

it('leaves an image that is already on disk alone', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);
    $before = file_get_contents(storage_path('app'.$wing->image_file));

    $this->artisan('wings:draw', ['wing' => $wing->id])
        ->expectsOutputToContain('on disk already')
        ->assertExitCode(0);

    expect(file_get_contents(storage_path('app'.$wing->image_file)))->toBe($before);

    $this->deleteWingImage($wing->image_file);
});

it('redraws only the missing images with --missing', function () {
    $user = User::factory()->create();
    $kept = $this->wingOwnedBy($user);
    $lost = $this->wingOwnedBy($user);

    unlink(storage_path('app'.$lost->image_file));

    $this->artisan('wings:draw --missing')
        ->expectsOutputToContain('1 drawn')
        ->assertExitCode(0);

    expect(File::exists(storage_path('app'.$lost->image_file)))->toBeTrue()
        ->and(File::exists(storage_path('app'.$kept->image_file)))->toBeTrue();

    $this->deleteWingImage($lost->image_file);
    $this->deleteWingImage($kept->image_file);
});

it('asks for a target when it gets none', function () {
    $this->artisan('wings:draw')->assertExitCode(1);
});
