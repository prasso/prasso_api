<?php

namespace Database\Factories;

use App\Models\TeamImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TeamImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'team_id' => 0,
            'path' => $this->faker->text(),
        ];
    }
}
