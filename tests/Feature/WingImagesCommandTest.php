<?php

use App\User;
use Illuminate\Support\Facades\File;

it('lists the wings whose image file is missing', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);

    $this->artisan('wings:images')->assertExitCode(0);

    unlink(storage_path('app'.$wing->image_file));

    $this->artisan('wings:images --missing --paths')
        ->expectsOutputToContain($wing->image_file)
        ->assertExitCode(0);

    $this->deleteWingImage($wing->image_file);
});

it('copies the missing images from another checkout', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);

    $source = storage_path('framework/testing/wingimg');
    $relative = substr($wing->image_file, strlen('/wingimg/'));
    File::ensureDirectoryExists($source.'/'.dirname($relative));
    File::copy(storage_path('app'.$wing->image_file), $source.'/'.$relative);
    unlink(storage_path('app'.$wing->image_file));

    $this->artisan('wings:images --copy-from='.$source)
        ->assertExitCode(0);

    expect(File::exists(storage_path('app'.$wing->image_file)))->toBeTrue();
    expect(file_get_contents(storage_path('app'.$wing->image_file)))->toBe('original image');

    File::deleteDirectory($source);
    $this->deleteWingImage($wing->image_file);
});
