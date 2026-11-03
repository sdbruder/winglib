<?php

namespace App\Console\Commands;

use App\Wing;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Description('List the wing images the database references and copy the missing ones')]
#[Signature('wings:images
                            {--missing : list only the images whose file is not on disk}
                            {--paths : print the referenced paths instead of a table}
                            {--copy-from= : copy the missing files out of the storage/app/wingimg directory of another checkout}')]
class WingImages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $root = storage_path('app/wingimg');
        $source = $this->option('copy-from');
        $referenced = [];
        $blank = 0;

        foreach (Wing::query()->orderBy('id')->get(['id', 'slug', 'image_file']) as $wing) {
            $image = ltrim((string) $wing->image_file, '/');

            if ($image === '') {
                $blank++;

                continue;
            }

            $relative = str_starts_with($image, 'wingimg/') ? substr($image, strlen('wingimg/')) : null;

            $referenced[] = [
                'id' => $wing->id,
                'slug' => $wing->slug,
                'image' => '/'.$image,
                'path' => $relative === null ? null : $root.'/'.$relative,
                'relative' => $relative,
                'exists' => $relative !== null && File::exists($root.'/'.$relative),
            ];
        }

        $missing = array_values(array_filter($referenced, fn (array $row) => ! $row['exists']));

        if ($this->option('paths')) {
            foreach ($this->option('missing') ? $missing : $referenced as $row) {
                $this->line($row['image']);
            }
        } else {
            $rows = $this->option('missing') ? $missing : $referenced;

            $this->table(
                ['id', 'slug', 'image', 'on disk'],
                array_map(fn (array $row) => [$row['id'], $row['slug'], $row['image'], $row['exists'] ? 'yes' : 'no'], $rows)
            );
        }

        $copied = 0;
        $failed = 0;

        if ($source !== null) {
            foreach ($missing as $row) {
                if ($row['relative'] === null) {
                    $this->warn(sprintf('wing %d keeps its image outside wingimg: %s', $row['id'], $row['image']));
                    $failed++;

                    continue;
                }

                $from = rtrim($source, '/').'/'.$row['relative'];

                if (! File::exists($from)) {
                    $failed++;

                    continue;
                }

                File::ensureDirectoryExists(dirname($row['path']));
                File::copy($from, $row['path']);
                $copied++;
            }
        }

        $this->line(sprintf(
            '%d wings, %d referencing an image, %d missing on disk%s.%s',
            count($referenced) + $blank,
            count($referenced),
            count($missing),
            $source === null ? '' : sprintf(', %d copied', $copied),
            $blank > 0 ? sprintf(' %d wing(s) have no image recorded.', $blank) : ''
        ));

        return $source !== null && $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
