<?php

declare(strict_types=1);

use RectorLaravel\Set\LaravelLevelSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    // The level set is the one upgrade set up to the major this hop installs. It is written out
    // rather than derived from composer.json, because the constant that derivation needs
    // (LaravelSetList::COMPOSER_BASED) does not exist in rector-laravel 2.5.0, the version the
    // PHP 7.4 stage resolves.
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_60,
    ]);
