<?php

use App\User;
use Illuminate\Support\Facades\File;

it('draws a smaller sibling with --width', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);
    $full = storage_path('app'.$wing->image_file);
    $thumb = storage_path('app'.$wing->imageVariant(90));

    expect($wing->imageVariant(90))->toBe(preg_replace('/\.png$/', '-90.png', $wing->image_file));

    $this->artisan('wings:draw', ['wing' => $wing->id, '--width' => 90])
        ->expectsOutputToContain('-90.png')
        ->assertExitCode(0);

    expect(File::exists($thumb))->toBeTrue();

    $size = getimagesize($thumb);

    expect($size[0])->toBe(90)->and($size[1])->toBe(38);
    expect(file_get_contents($thumb))->not->toBe(file_get_contents($full));

    File::delete($thumb);
    $this->deleteWingImage($wing->image_file);
});

it('leaves the full size image alone when drawing a thumbnail', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);
    $before = file_get_contents(storage_path('app'.$wing->image_file));

    $this->artisan('wings:draw', ['wing' => $wing->id, '--width' => 188])->assertExitCode(0);

    expect(file_get_contents(storage_path('app'.$wing->image_file)))->toBe($before);

    File::delete(storage_path('app'.$wing->imageVariant(188)));
    $this->deleteWingImage($wing->image_file);
});
