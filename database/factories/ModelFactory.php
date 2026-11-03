<?php

use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => bcrypt(Str::random(10)),
        'remember_token' => Str::random(10),
    ];
});

$factory->define(App\Wing::class, function (Faker\Generator $faker) {
    $wing = [
        'name' => 'Test Wing '.$faker->unique()->word,
        'description' => $faker->sentence,
        'image_file' => '/wingimg/00/test.png',
        'unitsystem' => 'metric',
        'cgpos' => 12,
        'weight' => 500,
        'drawvalues' => 0,
        'rootchord' => 340,
        'wingarea' => 100,
        'macdist' => 100,
        'maclength' => 180,
        'cg' => 20,
        'wingload' => 50,
    ];

    // The 24 panel fields: one span of 1..6 and a fixed chord, sweep and angle per panel.
    for ($panel = 1; $panel <= 6; $panel++) {
        $wing['panel'.$panel.'span'] = $panel;
        $wing['panel'.$panel.'chord'] = 10;
        $wing['panel'.$panel.'sweep'] = 0;
        $wing['panel'.$panel.'angle'] = 0;
    }

    return $wing;
});
