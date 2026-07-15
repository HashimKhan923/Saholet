<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class GenerateDueSubscriptionVisits extends Command
{
    protected $signature = 'subscriptions:generate-due-visits';

    protected $description = 'Create the next visit Booking for every active subscription whose next_visit_date has arrived';

    public function handle(SubscriptionService $subscriptions): int
    {
        $due = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('provider_profile_id')
            ->whereDate('next_visit_date', '<=', now()->toDateString())
            ->get();

        $created = 0;

        foreach ($due as $subscription) {
            if ($subscriptions->generateDueVisit($subscription)) {
                $created++;
            }
        }

        $this->info("Generated {$created} visit(s) for " . $due->count() . ' due subscription(s).');

        return self::SUCCESS;
    }
}
