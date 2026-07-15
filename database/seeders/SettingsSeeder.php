<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(['key' => 'commission_rate'], ['value' => '10']);
        Setting::updateOrCreate(['key' => 'geofencing_enabled'], ['value' => '0']);
    }
}