<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Prasso\Church\Models\EventType;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventTypes = [
            [
                'name' => 'Worship Service',
                'slug' => 'service',
                'description' => 'Regular worship services and ceremonies',
                'color' => '#3B82F6',
                'icon' => 'heroicon-o-church',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Meeting',
                'slug' => 'meeting',
                'description' => 'Business meetings and gatherings',
                'color' => '#10B981',
                'icon' => 'heroicon-o-users',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Class/Study',
                'slug' => 'class',
                'description' => 'Educational classes and Bible studies',
                'color' => '#F59E0B',
                'icon' => 'heroicon-o-academic-cap',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Outreach',
                'slug' => 'outreach',
                'description' => 'Community outreach events and programs',
                'color' => '#EF4444',
                'icon' => 'heroicon-o-heart',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Fellowship',
                'slug' => 'fellowship',
                'description' => 'Social events and fellowship activities',
                'color' => '#8B5CF6',
                'icon' => 'heroicon-o-user-group',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'description' => 'Other types of events',
                'color' => '#6B7280',
                'icon' => 'heroicon-o-calendar',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($eventTypes as $eventType) {
            EventType::firstOrCreate(
                ['slug' => $eventType['slug']],
                $eventType
            );
        }
    }
}
