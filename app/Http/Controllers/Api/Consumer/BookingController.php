<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function __construct(
        private WalletService $wallets,
        private \App\Services\GeofenceService $geofence,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::with(['service', 'providerProfile.user'])
            ->where('consumer_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'bookings' => BookingResource::collection($bookings),
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        $booking->load(['service.category', 'providerProfile.user', 'payments', 'review', 'dispute']);

        return response()->json(['booking' => new BookingResource($booking)]);
    }

    /**
     * Create a direct booking with a specific approved provider for a service they offer.
     * Body: provider_profile_id, service_id, scheduled_date, scheduled_time, address, latitude?, longitude?, notes?
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'provider_profile_id' => ['required', 'exists:provider_profiles,id'],
            'service_id' => ['required', 'exists:services,id'],
        ]);

        $provider = ProviderProfile::findOrFail($validated['provider_profile_id']);
        $service = Service::findOrFail($validated['service_id']);
        $providerService = $this->resolveOffering($provider, $service);

        if (! $this->geofence->isAllowed($provider->city)) {
            return response()->json(['message' => 'Sorry, this provider is outside our current service areas.'], 422);
        }

        $dateValues = array_column($this->bookableDates(), 'value');

        $data = $request->validate([
            'scheduled_date' => ['required', 'date', Rule::in($dateValues)],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $slots = collect($this->availableSlotsFor($provider, Carbon::parse($data['scheduled_date'])));
        $slot = $slots->firstWhere('value', $data['scheduled_time']);

        if (! $slot || ! $slot['available']) {
            return response()->json(['message' => 'That time slot is no longer available. Please choose another.'], 422);
        }

        $booking = Booking::create([
            'reference' => $this->generateReference(),
            'consumer_id' => $request->user()->id,
            'corporate_account_id' => $request->user()->corporate_account_id,
            'provider_profile_id' => $provider->id,
            'service_id' => $service->id,
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $data['scheduled_time'],
            'price' => $providerService->price,
            'duration_minutes' => $service->duration_minutes,
            'address' => $data['address'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => Booking::STATUS_PENDING,
        ]);

        app(\App\Services\Notifier::class)->notify(
            $provider->user,
            'booking',
            'New booking request',
            'You have a new booking (' . $booking->reference . ') for ' . $service->name . '.',
            route('provider.bookings.show', $booking)
        );

        $booking->load(['service.category', 'providerProfile.user']);

        return response()->json(['booking' => new BookingResource($booking)], 201);
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('cancel', $booking);

        if (! $booking->canBeCancelledByConsumer()) {
            return response()->json(['message' => 'This booking can no longer be cancelled.'], 422);
        }

        $booking->load(['providerProfile.user', 'payments']);
        $payment = $booking->activePayment();
        if ($payment && $payment->isEscrow()) {
            $this->wallets->refund($payment, $booking->providerProfile->user);
        }

        $booking->update([
            'status' => Booking::STATUS_CANCELLED,
            'cancelled_by' => 'consumer',
            'cancelled_at' => now(),
        ]);

        app(\App\Services\Notifier::class)->notify(
            $booking->providerProfile->user,
            'booking',
            'Booking cancelled',
            'Booking ' . $booking->reference . ' was cancelled by the customer.',
            route('provider.bookings.show', $booking)
        );

        return response()->json([
            'message' => 'Booking cancelled.' . ($payment && $payment->isRefunded() ? ' Your escrow payment has been refunded.' : ''),
            'booking' => new BookingResource($booking->fresh(['service', 'providerProfile.user', 'payments'])),
        ]);
    }

    private function resolveOffering(ProviderProfile $provider, Service $service): ProviderService
    {
        abort_unless($provider->isApproved(), 404);
        abort_unless($service->is_active, 404);

        $offering = ProviderService::where('provider_profile_id', $provider->id)
            ->where('service_id', $service->id)
            ->where('is_active', true)
            ->first();

        abort_unless($offering, 404);

        return $offering;
    }

    private function bookableDates(): array
    {
        $days = (int) config('booking.advance_days');
        $dates = [];

        for ($i = 0; $i <= $days; $i++) {
            $d = now()->startOfDay()->addDays($i);
            $dates[] = ['value' => $d->toDateString()];
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
                'available' => ! $isPast && ! $isTaken,
            ];

            $cursor->addMinutes($interval);
        }

        return $slots;
    }

    private function generateReference(): string
    {
        do {
            $ref = 'BK-' . strtoupper(Str::random(6));
        } while (Booking::where('reference', $ref)->exists());

        return $ref;
    }
}
