<?php

namespace Database\Seeders;

use App\Models\ServiceArea;
use Illuminate\Database\Seeder;

class ServiceAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['name' => 'Karachi Metro', 'city' => 'Karachi'],
            ['name' => 'Lahore Metro', 'city' => 'Lahore'],
            ['name' => 'Islamabad Capital', 'city' => 'Islamabad'],
        ];

        foreach ($areas as $area) {
            ServiceArea::updateOrCreate(
                ['city' => $area['city']],
                ['name' => $area['name'], 'is_active' => true]
            );
        }
    }
}