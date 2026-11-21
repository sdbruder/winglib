<?php

namespace Tests;

use App\User;
use App\Wing;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use Tests\Support\StubGravatar;

class TestCase extends BaseTestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        // A cached configuration is written from .env by the optimize script that composer runs
        // on install and update. It quietly overrides the environment phpunit.xml sets, so the
        // suite would then run against the developer's app.url and database, and cached routes
        // would hide changes to the route files. Drop both, so every test boots live config.
        foreach (glob(__DIR__.'/../bootstrap/cache/{config,routes-*}.php', GLOB_BRACE) as $cached) {
            @unlink($cached);
        }

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // The gravatar package asks www.gravatar.com over HTTP for every saved user that has an
        // address. A test run must not depend on that service, so bind a stub that answers "no".
        $app->instance('gravatar', new StubGravatar);

        return $app;
    }

    /**
     * The view data a response was rendered with.
     *
     * TestResponse::viewData() arrives in a later Laravel major, but the property it reads is
     * the same in every version this suite runs on.
     *
     * @param  TestResponse  $response
     * @param  string  $key
     * @return mixed
     */
    protected function viewData($response, $key)
    {
        return $response->original->getData()[$key];
    }

    /**
     * The twenty four panel fields the wing form posts.
     *
     * @return array
     */
    protected function panelFields()
    {
        $fields = [];

        for ($panel = 1; $panel <= 6; $panel++) {
            $fields['panel'.$panel.'span'] = $panel;
            $fields['panel'.$panel.'chord'] = 10;
            $fields['panel'.$panel.'sweep'] = 0;
            $fields['panel'.$panel.'angle'] = 0;
        }

        return $fields;
    }

    /**
     * Create every image directory the model writes into.
     *
     * Wing::createImage() and Wing::duplicateImage() write to storage/app/wingimg/<two digits>/,
     * where the digits come from substr(microtime(), 4, 2). The application never creates those
     * directories: a deployed storage/app already has them. A fresh checkout does not, so the
     * suite makes them, or the writes fail.
     *
     * @return void
     */
    protected function createWingImageDirectories()
    {
        for ($digit = 0; $digit <= 99; $digit++) {
            $directory = storage_path('app/wingimg/'.str_pad($digit, 2, '0', STR_PAD_LEFT));

            if (! is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
        }
    }

    /**
     * A data URL holding the given bytes, with the image directories ready for the write.
     *
     * @param  string  $bytes
     * @return string
     */
    protected function base64Png($bytes)
    {
        $this->createWingImageDirectories();

        return 'data:image/png;base64,'.base64_encode($bytes);
    }

    /**
     * Remove the file a wing image URL points at.
     *
     * Wing::createImage() writes real files that a database transaction cannot roll back.
     *
     * @param  string  $imageUrl
     * @return void
     */
    protected function deleteWingImage($imageUrl)
    {
        $path = storage_path('app'.$imageUrl);

        if (file_exists($path)) {
            unlink($path);
        }

        foreach (Wing::IMAGE_VARIANTS as $width) {
            $variant = storage_path('app'.(new Wing(['image_file' => $imageUrl]))->imageVariant($width));

            if (file_exists($variant)) {
                unlink($variant);
            }
        }
    }

    /**
     * A wing that belongs to the given user, with a real image file behind it.
     *
     * @return Wing
     */
    protected function wingOwnedBy(User $user)
    {
        return Wing::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Wing Alpha',
            'image_file' => Wing::createImage($this->base64Png('original image')),
        ]);
    }

    /**
     * A wing with a fresh user of its own.
     *
     * @return Wing
     */
    protected function createWing(array $attributes = [])
    {
        $user = User::factory()->create();

        return Wing::factory()->create($attributes + ['user_id' => $user->id]);
    }

    /**
     * The value of one query parameter of a URL.
     *
     * @param  string  $url
     * @param  string  $key
     * @return string|null
     */
    protected function queryValue($url, $key)
    {
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        return $query[$key] ?? null;
    }
}
