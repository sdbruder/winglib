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

it('counts a wing that has no image file recorded', function () {
    // Both of the command's uncovered mutants sit in the blank image_file branch: the counter and
    // the skip. A third wing with an image, ordered after the blank one, is what tells the skip
    // apart from leaving the loop - with a break it would be dropped from the referencing count.
    $user = User::factory()->create();
    $first = $this->wingOwnedBy($user);
    $blank = $this->createWing(['name' => 'No Image Wing']);

    $blank->image_file = '';
    $blank->saveQuietly();

    $last = $this->wingOwnedBy($user);

    $this->artisan('wings:images')
        ->expectsOutputToContain('3 wings, 2 referencing an image, 0 missing on disk. 1 wing(s) have no image recorded.')
        ->assertExitCode(0);

    $this->deleteWingImage($first->image_file);
    $this->deleteWingImage($last->image_file);
});
