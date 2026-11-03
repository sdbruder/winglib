<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. Only the local driver, which needs no extra package,
    | is configured here: the application writes its wing images with plain
    | file functions under storage/app, and uses no disk at all. The cloud
    | key is gone in Laravel 9, and the s3 and ftp disks would each need a
    | Flysystem 3 driver package that is not installed.
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],

    ],

];
