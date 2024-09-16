<?php

namespace Database\Factories;

use App\Models\SitePageData;
use Illuminate\Database\Eloquent\Factories\Factory;

class SitePageDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SitePageData::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'data_key' => $this->faker->name,
            'fk_site_page_id' => false,
            'json_data' => '{}'
        ];
    }
}
