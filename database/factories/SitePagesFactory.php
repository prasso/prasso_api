<?php

namespace Database\Factories;

use App\Models\SitePages;
use Illuminate\Database\Eloquent\Factories\Factory;

class SitePagesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SitePages::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->name,
            'fk_site_id' => false,
            'section' => 'testsection',
            'description' => $this->faker->phoneNumber,
            'url' => 'testSitePages.test',
            'headers' =>  'Authorization: BearerToken',
            'masterpage' => '' ,
            'template' =>  '',
            'style' =>  '',
            'login_required' =>  1,
            'user_level' =>  0,
        ];
    }
}
