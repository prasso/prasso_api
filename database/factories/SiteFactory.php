<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Site::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'site_name' => $this->faker->name,
            'host' => 'testsite.test',
            'main_color' =>  '#000000',
            'logo_image' => 'image.jpg' ,
            'database' =>  'mysql',
            'app_specific_js' =>  '',
            'app_specific_css' =>  '',
            'image_folder' =>  'testing',
            'favicon' =>  'favicon.ico',
            'description' =>  'created for unit tests',
            'supports_registration' =>  1,
            'subteams_enabled' => 1
        ];
    }
}