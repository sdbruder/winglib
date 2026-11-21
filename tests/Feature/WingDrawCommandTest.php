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

it('names the id when no wing has it', function () {
    $this->artisan('wings:draw', ['wing' => 999999])
        ->expectsOutputToContain('there is no wing with id 999999')
        ->assertExitCode(1);
});

it('says there is nothing to do when --missing finds every image on disk', function () {
    $user = User::factory()->create();
    $wing = $this->wingOwnedBy($user);

    $this->artisan('wings:draw --missing')
        ->expectsOutputToContain('no wing to draw')
        ->assertExitCode(0);

    $this->deleteWingImage($wing->image_file);
});

it('skips a wing it cannot draw and carries on with the rest', function () {
    $user = User::factory()->create();
    $good = $this->wingOwnedBy($user);
    $broken = $this->createWing(['name' => 'Broken Wing']);

    // A wing with no usable panel throws out of the drawing, which must not stop the batch.
    $broken->panels = array_fill(1, 6, ['span' => '0', 'chord' => '0', 'sweep' => '0', 'angle' => '0']);
    $broken->saveQuietly();

    // A third wing, ordered after the broken one, so that leaving the loop instead of skipping
    // would show up as one fewer drawn rather than an identical report.
    $after = $this->wingOwnedBy($user);

    unlink(storage_path('app'.$good->image_file));
    unlink(storage_path('app'.$after->image_file));

    $this->artisan('wings:draw --all --force')
        ->expectsOutputToContain('skipped,')
        ->expectsOutputToContain('2 drawn, 0 already on disk, 1 skipped')
        ->assertExitCode(0);

    expect(File::exists(storage_path('app'.$good->image_file)))->toBeTrue()
        ->and(File::exists(storage_path('app'.$after->image_file)))->toBeTrue();

    $this->deleteWingImage($good->image_file);
    $this->deleteWingImage($after->image_file);
    $this->deleteWingImage($broken->image_file);
});

it('gives a wing with no image file a fresh path and draws it', function () {
    $user = User::factory()->create();
    $wing = $this->createWing(['name' => 'No Image Wing']);

    $wing->image_file = '';
    $wing->saveQuietly();

    $this->artisan('wings:draw', ['wing' => $wing->id])->assertExitCode(0);

    $wing->refresh();

    // pathFor cuts two characters out of microtime() for the subdirectory, the same shape
    // createImage uses: two digits, so a shifted offset or a dropped substr shows up here.
    expect($wing->image_file)->toMatch('#^/wingimg/\d\d/#');
    expect(File::exists(storage_path('app'.$wing->image_file)))->toBeTrue();

    $this->deleteWingImage($wing->image_file);
});
