<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            [
                'name' => 'AC Repair & Service',
                'icon' => 'ac',
                'description' => 'Installation, servicing, gas refill and repair for split and window units.',
                'services' => [
                    ['AC General Service', 'Complete cleaning and performance check for one unit.', 1500, 60],
                    ['AC Gas Refill', 'Top-up or full refill of refrigerant gas.', 3500, 90],
                    ['AC Installation', 'Mounting and setup of a new split unit.', 4000, 120],
                ],
            ],
            [
                'name' => 'Plumbing',
                'icon' => 'plumbing',
                'description' => 'Leak fixes, fittings, drainage and general plumbing work.',
                'services' => [
                    ['Leak Repair', 'Diagnose and fix a leaking pipe or fitting.', 1200, 60],
                    ['Tap & Faucet Installation', 'Replace or install taps and mixers.', 900, 45],
                    ['Drain Unclogging', 'Clear blocked drains and pipes.', 1800, 60],
                ],
            ],
            [
                'name' => 'Electrical',
                'icon' => 'electrical',
                'description' => 'Wiring, fixtures, switches and electrical fault finding.',
                'services' => [
                    ['Switchboard Repair', 'Fix faulty switches, sockets and boards.', 1000, 45],
                    ['Light & Fan Installation', 'Mount and wire ceiling fans or light fixtures.', 1200, 60],
                    ['Wiring Fault Diagnosis', 'Trace and resolve electrical faults.', 1500, 90],
                ],
            ],
            [
                'name' => 'Home Cleaning',
                'icon' => 'cleaning',
                'description' => 'Deep cleaning for homes, kitchens and bathrooms.',
                'services' => [
                    ['Full Home Deep Clean', 'Top-to-bottom cleaning for up to a 3-bed home.', 6000, 240],
                    ['Kitchen Deep Clean', 'Degreasing and detailed kitchen cleaning.', 2500, 120],
                    ['Bathroom Deep Clean', 'Descaling and sanitising per bathroom.', 1500, 60],
                ],
            ],
            [
                'name' => 'Carpentry & Repairs',
                'icon' => 'carpentry',
                'description' => 'Furniture repair, fittings and general carpentry.',
                'services' => [
                    ['Furniture Repair', 'Fix loose joints, hinges and surfaces.', 1500, 90],
                    ['Door Lock Installation', 'Install or replace door locks and handles.', 1000, 45],
                ],
            ],
        ];

        foreach ($catalog as $order => $cat) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'icon' => $cat['icon'],
                    'is_active' => true,
                    'sort_order' => $order,
                ]
            );

            foreach ($cat['services'] as [$name, $desc, $price, $duration]) {
                Service::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => $desc,
                        'base_price' => $price,
                        'duration_minutes' => $duration,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}