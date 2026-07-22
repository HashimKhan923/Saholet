<?php

namespace Database\Seeders;

use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::where('is_active', true)->get();
        $cities = ['Karachi'];

        for ($i = 1; $i <= 20; $i++) {
            $user = User::updateOrCreate(
                ['email' => "provider{$i}@sahoulat.com"],
                [
                    'name' => "Provider {$i}",
                    'phone' => '+92300' . str_pad((string) $i, 7, '0', STR_PAD_LEFT),
                    'role' => User::ROLE_PROVIDER,
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            $profile = ProviderProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name' => "Provider {$i} Services",
                    'bio' => 'Experienced home services professional.',
                    'experience_years' => random_int(1, 15),
                    'city' => $cities[array_rand($cities)],
                    'address' => "Street " . random_int(1, 200) . ", " . $cities[array_rand($cities)],
                    'cnic_number' => (string) random_int(10000_0000000, 99999_9999999),
                    'status' => ProviderProfile::STATUS_APPROVED,
                    'submitted_at' => now(),
                    'reviewed_at' => now(),
                ]
            );

            $randomServices = $services->random(min(random_int(2, 5), $services->count()));

            foreach ($randomServices as $service) {
                $variance = random_int(-20, 40); // percent above/below base price
                $price = max(200, round($service->base_price * (1 + $variance / 100), -1));

                ProviderService::updateOrCreate(
                    [
                        'provider_profile_id' => $profile->id,
                        'service_id' => $service->id,
                    ],
                    [
                        'price' => $price,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
