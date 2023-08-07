<?php

namespace Database\Factories;

use App\Models\Apps;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Apps::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {   
        return [
            'team_id' =>  0,
            'site_id'  =>  0,
            'appicon' =>  $this->faker->name, 
            'app_name' => $this->faker->name, 
            'page_title' =>  $this->faker->name, 
            'page_url' => $this->faker->name , 
            'sort_order' =>  1, 
   
        ];
    }
}
