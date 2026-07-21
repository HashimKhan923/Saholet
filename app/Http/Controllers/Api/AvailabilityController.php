<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AvailabilityController extends Controller
{
    /**
     * Bookable dates (next N days per config) and time slots for a given date,
     * for a specific provider + service pairing. Used to render the booking picker.
     */
    public function show(Request $request, ProviderProfile $provider, Service $service): JsonResponse
    {
        abort_unless($provider->isApproved(), 404);
        abort_unless($service->is_active, 404);

        $offering = ProviderService::where('provider_profile_id', $provider->id)
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->first();

        abort_unless($offering, 404);

        $dates = $this->bookableDates();
        $dateValues = array_column($dates, 'value');

        $selectedDate = $request->query('date');
        if (! in_array($selectedDate, $dateValues, true)) {
            $selectedDate = $dateValues[0];
        }

        return response()->json([
            'price' => (float) $offering->price,
            'duration_minutes' => $service->duration_minutes,
            'dates' => $dates,
            'selected_date' => $selectedDate,
            'slots' => $this->availableSlotsFor($provider, Carbon::parse($selectedDate)),
        ]);
    }

    private function bookableDates(): array
    {
        $days = (int) config('booking.advance_days');
        $dates = [];

        for ($i = 0; $i <= $days; $i++) {
            $d = now()->startOfDay()->addDays($i);
            $dates[] = [
                'value' => $d->toDateString(),
                'label' => $d->isToday() ? 'Today — ' . $d->format('d M') : $d->format('D, d M'),
            ];
        }

        return $dates;
    }

    private function availableSlotsFor(ProviderProfile $provider, Carbon $date): array
    {
        $startHour = (int) config('booking.slot_start_hour');
        $endHour = (int) config('booking.slot_end_hour');
        $interval = (int) config('booking.slot_interval_minutes');
        $minLead = (int) config('booking.min_lead_hours');

        $taken = Booking::where('provider_profile_id', $provider->id)
            ->where('scheduled_date', $date->toDateString())
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->pluck('scheduled_time')
            ->map(fn ($t) => substr($t, 0, 5))
            ->all();

        $threshold = now()->addHours($minLead);

        $slots = [];
        $cursor = $date->copy()->setTime($startHour, 0);
        $end = $date->copy()->setTime($endHour, 0);

        while ($cursor < $end) {
            $value = $cursor->format('H:i');
            $isPast = $cursor->lt($threshold);
            $isTaken = in_array($value, $taken, true);

            $slots[] = [
                'value' => $value,
                'label' => $cursor->format('g:i A'),
                'available' => ! $isPast && ! $isTaken,
            ];

            $cursor->addMinutes($interval);
        }

        return $slots;
    }
}
