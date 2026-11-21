<?php

namespace App\Console\Commands;

use App\Wing;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('wings:images
    {--missing : list only the images whose file is not on disk}
    {--paths : print the referenced paths instead of a table}')]
#[Description('List the wing image files the database references, and which are missing')]
class WingImages extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $root = storage_path('app/wingimg');
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

        $this->line(sprintf(
            '%d wings, %d referencing an image, %d missing on disk.%s',
            count($referenced) + $blank,
            count($referenced),
            count($missing),
            $blank > 0 ? sprintf(' %d wing(s) have no image recorded.', $blank) : ''
        ));

        return self::SUCCESS;
    }
}
