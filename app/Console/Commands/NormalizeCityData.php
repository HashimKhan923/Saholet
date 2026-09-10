<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\CareerListing;
use App\Models\Contract;
use App\Models\EmergencyRequest;
use App\Models\JobPost;
use App\Models\JobSeekerProfile;
use App\Models\Order;
use App\Models\ProviderProfile;
use App\Models\Subscription;
use App\Support\CityNormalizer;
use Illuminate\Console\Command;

/**
 * One-time backfill for city values saved before NormalizesCity existed — e.g.
 * "karachi" / "KARACHI " / "Karachi" all stored as distinct strings, which showed
 * up as duplicate entries in city filters. Re-saving each row runs it through the
 * model's NormalizesCity trait, same as any new write now does automatically.
 */
class NormalizeCityData extends Command
{
    protected $signature = 'cities:normalize';

    protected $description = 'Normalize case/whitespace of existing city values across all tables that store city as free text';

    public function handle(): int
    {
        $targets = [
            [ProviderProfile::class, 'city'],
            [Address::class, 'city'],
            [JobSeekerProfile::class, 'city'],
            [CareerListing::class, 'city'],
            [JobPost::class, 'city'],
            [Contract::class, 'city'],
            [Subscription::class, 'city'],
            [EmergencyRequest::class, 'city'],
            [Order::class, 'shipping_city'],
        ];

        foreach ($targets as [$modelClass, $column]) {
            $updated = 0;

            $modelClass::query()->whereNotNull($column)->chunkById(200, function ($rows) use ($column, &$updated) {
                foreach ($rows as $row) {
                    $normalized = CityNormalizer::normalize($row->{$column});

                    if ($normalized !== $row->{$column}) {
                        $row->{$column} = $normalized;
                        $row->saveQuietly();
                        $updated++;
                    }
                }
            });

            $this->info("{$modelClass}: normalized {$updated} row(s).");
        }

        return self::SUCCESS;
    }
}
