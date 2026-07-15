<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates the recurring visit Bookings behind an AMC/subscription plan.
 * A visit is a normal Booking (subscription_id set) that flows through the
 * exact same payment/escrow/chat/tracking/dispute code as any other booking
 * — the only new logic here is deciding *when* the next one is due.
 */
class SubscriptionService
{
    public function assignProvider(Subscription $subscription, ProviderProfile $provider, string $scheduledTime): Booking
    {
        $subscription->loadMissing('plan.service');

        return DB::transaction(function () use ($subscription, $provider, $scheduledTime) {
            $subscription->update([
                'provider_profile_id' => $provider->id,
                'status' => Subscription::STATUS_ACTIVE,
            ]);

            return $this->scheduleVisit($subscription, $scheduledTime);
        });
    }

    public function generateDueVisit(Subscription $subscription): ?Booking
    {
        if (! $subscription->isActive() || ! $subscription->provider_profile_id) {
            return null;
        }

        if (! $subscription->hasVisitsRemaining()) {
            $subscription->update(['status' => Subscription::STATUS_COMPLETED]);

            return null;
        }

        return DB::transaction(fn () => $this->scheduleVisit($subscription));
    }

    private function scheduleVisit(Subscription $subscription, ?string $scheduledTime = null): Booking
    {
        $subscription->loadMissing('plan.service');
        $plan = $subscription->plan;

        $booking = Booking::create([
            'reference' => $this->generateBookingReference(),
            'consumer_id' => $subscription->consumer_id,
            'corporate_account_id' => $subscription->corporate_account_id,
            'provider_profile_id' => $subscription->provider_profile_id,
            'service_id' => $plan->service_id,
            'subscription_id' => $subscription->id,
            'scheduled_date' => $subscription->next_visit_date,
            'scheduled_time' => $scheduledTime ?? '10:00',
            'price' => $plan->price_per_visit,
            'duration_minutes' => $plan->service->duration_minutes,
            'address' => $subscription->address,
            'latitude' => $subscription->latitude,
            'longitude' => $subscription->longitude,
            'status' => Booking::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        $visitsUsed = $subscription->visits_used + 1;
        $nextVisitDate = $subscription->next_visit_date->copy()->addMonths($plan->frequency_months);

        $stillHasVisits = $plan->total_visits === null || $visitsUsed < $plan->total_visits;

        $subscription->update([
            'visits_used' => $visitsUsed,
            'next_visit_date' => $nextVisitDate,
            'status' => $stillHasVisits ? Subscription::STATUS_ACTIVE : Subscription::STATUS_COMPLETED,
        ]);

        app(Notifier::class)->notify(
            $subscription->consumer,
            'subscription',
            'A new service visit is scheduled',
            $plan->name . ' — visit scheduled for ' . $booking->scheduled_date->format('d M Y') . '. Please complete payment to confirm.',
            route('consumer.bookings.show', $booking)
        );

        return $booking;
    }

    private function generateBookingReference(): string
    {
        do {
            $ref = 'BK-' . strtoupper(Str::random(6));
        } while (Booking::where('reference', $ref)->exists());

        return $ref;
    }
}
