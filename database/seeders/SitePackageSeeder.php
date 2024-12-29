<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SitePackage;
use Illuminate\Support\Str;

class SitePackageSeeder extends Seeder
{
    public function run()
    {
        $packages = [
            [
                'name' => 'AutoProHub',
                'slug' => 'autoprohub',
                'description' => 'Automotive management features and tools',
                'is_active' => true
            ],
            [
                'name' => 'Basic Site',
                'slug' => 'basic-site',
                'description' => 'Basic site features including content management',
                'is_active' => true
            ],
            [
                'name' => 'Advanced Analytics',
                'slug' => 'advanced-analytics',
                'description' => 'Enhanced analytics and reporting tools',
                'is_active' => true
            ]
        ];

        foreach ($packages as $package) {
            SitePackage::create($package);
        }
    }
}
