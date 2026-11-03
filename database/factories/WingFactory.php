<?php

namespace Database\Factories;

use App\Wing;
use Illuminate\Database\Eloquent\Factories\Factory;

class WingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Wing::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $wing = [
            'name' => 'Test Wing '.$this->faker->unique()->word,
            'description' => $this->faker->sentence,
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
    }
}
