<?php

namespace Database\Seeders;

use App\Models\ServiceArea;
use Illuminate\Database\Seeder;

class ServiceAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['name' => 'Karachi', 'city' => 'Karachi'],
        ];

        foreach ($areas as $area) {
            ServiceArea::updateOrCreate(
                ['city' => $area['city']],
                ['name' => $area['name'], 'is_active' => true]
            );
        }
    }
}