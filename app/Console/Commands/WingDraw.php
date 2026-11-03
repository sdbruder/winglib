<?php

namespace App\Console\Commands;

use App\Support\WingDrawing;
use App\Wing;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use RuntimeException;

#[Signature('wings:draw
    {wing? : the id of the wing to redraw}
    {--missing : redraw the wings whose image file is not on disk}
    {--all : redraw every wing}
    {--force : overwrite images that are still on disk}')]
#[Description('Rebuild wing images from the stored measurements')]
class WingDraw extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $wings = $this->selectWings();

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
            $path = $this->pathFor($wing);

            if (File::exists($path) && ! $this->option('force')) {
                $this->line(sprintf('wing %d %s: on disk already', $wing->id, $wing->slug));
                $kept++;

                continue;
            }

            try {
                $png = WingDrawing::png($wing);
            } catch (RuntimeException $exception) {
                $this->warn(sprintf('wing %d %s: skipped, %s', $wing->id, $wing->slug, $exception->getMessage()));
                $skipped++;

                continue;
            }

            File::ensureDirectoryExists(dirname($path));
            File::put($path, $png);

            $this->line(sprintf('wing %d %s: drew %s (%d bytes)', $wing->id, $wing->slug, $wing->image_file, strlen($png)));
            $drawn++;
        }

        $this->info(sprintf('%d drawn, %d already on disk, %d skipped', $drawn, $kept, $skipped));

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, Wing>|null
     */
    private function selectWings(): ?Collection
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
            $wings = $wings->reject(fn (Wing $wing) => $this->hasFile($wing));
        }

        return $wings->values();
    }

    private function hasFile(Wing $wing): bool
    {
        return $wing->image_file !== '' && File::exists(storage_path('app'.$wing->image_file));
    }

    /**
     * Where the PNG goes: the wing's own url when it has one, otherwise a fresh one in the
     * layout createImage uses. saveQuietly keeps the saving hook from rebuilding the panels
     * and the slug from attributes this command never touches.
     */
    private function pathFor(Wing $wing): string
    {
        if ($wing->image_file !== '') {
            return storage_path('app'.$wing->image_file);
        }

        $subdir = substr(microtime(), 4, 2);
        $url = '/wingimg/'.$subdir.'/'.uniqid('').'.png';

        $wing->image_file = $url;
        $wing->saveQuietly();

        return storage_path('app'.$url);
    }
}
