<?php

use App\User;

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

it('says nothing when every referenced image is on disk', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);

    $this->artisan('wings:images --missing --paths')
        ->expectsOutputToContain('0 missing on disk')
        ->assertExitCode(0);

    $this->deleteWingImage($wing->image_file);
});
