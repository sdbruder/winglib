<?php

namespace App\Console\Commands;

use App\Support\WingDrawing;
use App\Wing;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;

#[Signature('wings:draw
    {wing? : the id of the wing to redraw}
    {--missing : redraw the wings whose image file is not on disk}
    {--all : redraw every wing}
    {--force : overwrite images that are still on disk}
    {--width=940 : the canvas width; below 940 the drawing goes to a -<width> sibling}')]
#[Description('Rebuild wing images from the stored measurements')]
class WingDraw extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $width = max(90, (int) $this->option('width'));
        $wings = $this->selectWings($width);

        if ($wings === null) {
            return self::FAILURE;
        }

        if ($wings->isEmpty()) {
            $this->info('no wing to draw');

            return self::SUCCESS;
        }

        $drawn = 0;
        $kept = 0;
        $skipped = 0;

        foreach ($wings as $wing) {
            $path = $this->pathFor($wing, $width);

            if (File::exists($path) && ! $this->option('force')) {
                $this->line(sprintf('wing %d %s: on disk already', $wing->id, $wing->slug));
                $kept++;

                continue;
            }

            try {
                // A thumbnail carries no measure labels: at that size they only smear.
                $png = WingDrawing::png($wing, $width, $width < WingDrawing::WIDTH ? false : null);
            } catch (\Throwable $exception) {
                // One unrenderable row (a wing with no panels, a degenerate one) must not stop
                // a batch of three thousand.
                $this->warn(sprintf('wing %d %s: skipped, %s', $wing->id, $wing->slug, $exception->getMessage()));
                $skipped++;

                continue;
            }

            File::ensureDirectoryExists(dirname($path));
            File::put($path, $png);

            if ($width === WingDrawing::WIDTH) {
                // The full size image changed, so the thumbnails have to follow it.
                $wing->refreshImageVariants();
            }

            $this->line(sprintf('wing %d %s: drew %s (%d bytes)', $wing->id, $wing->slug, $wing->imageVariant($width), strlen($png)));
            $drawn++;
        }

        $this->info(sprintf('%d drawn, %d already on disk, %d skipped', $drawn, $kept, $skipped));

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, Wing>|null
     */
    private function selectWings(int $width): ?Collection
    {
        $id = $this->argument('wing');
        $missing = (bool) $this->option('missing');
        $all = (bool) $this->option('all');

        if ($id === null && ! $missing && ! $all) {
            $this->error('name a wing id, or pass --missing or --all');

            return null;
        }

        if ($id !== null) {
            $wing = Wing::query()->find((int) $id);

            if ($wing === null) {
                $this->error('there is no wing with id '.$id);

                return null;
            }

            return new Collection([$wing]);
        }

        $wings = Wing::query()->orderBy('id')->get();

        if ($missing) {
            $wings = $wings->reject(fn (Wing $wing) => $this->hasFile($wing, $width));
        }

        return $wings->values();
    }

    private function hasFile(Wing $wing, int $width): bool
    {
        return $wing->image_file !== '' && File::exists(storage_path('app'.$wing->imageVariant($width)));
    }

    /**
     * Where the PNG goes: the wing's own url when it has one, otherwise a fresh one in the
     * layout createImage uses. saveQuietly keeps the saving hook from rebuilding the panels
     * and the slug from attributes this command never touches.
     */
    private function pathFor(Wing $wing, int $width): string
    {
        if ($wing->image_file === '') {
            $subdir = substr(microtime(), 4, 2);
            $wing->image_file = '/wingimg/'.$subdir.'/'.uniqid('').'.png';
            $wing->saveQuietly();
        }

        return storage_path('app'.$wing->imageVariant($width));
    }
}
