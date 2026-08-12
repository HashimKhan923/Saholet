<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ServiceAreaSeeder extends Seeder
{
    /**
     * No default rows — a service area now requires a drawn boundary (there's
     * no sensible polygon to fabricate here), so these are created by an admin
     * via /admin/service-areas/create instead of being seeded.
     */
    public function run(): void
    {
        //
    }
}
