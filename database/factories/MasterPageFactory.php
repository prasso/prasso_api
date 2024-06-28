<?php

namespace Database\Factories;

use App\Models\MasterPage;
use Illuminate\Database\Eloquent\Factories\Factory;

class MasterPageFactory extends Factory
{
    protected $model = MasterPage::class;

    public function definition()
    {
        return [
            'pagename' => $this->faker->unique()->word,
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'js' => $this->faker->randomHtml(),
            'css' => $this->faker->randomHtml(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}


