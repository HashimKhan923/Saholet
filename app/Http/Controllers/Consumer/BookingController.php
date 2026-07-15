<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
        public function __construct(
        private WalletService $wallets,
        private \App\Services\GeofenceService $geofence,
    ) {}

    public function index(Request $request): View
    {
        $bookings = Booking::with(['service', 'providerProfile.user'])
            ->where('consumer_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('consumer.bookings.index', compact('bookings'));
    }

    public function create(Request $request, ProviderProfile $provider, Service $service): View
    {
        $providerService = $this->resolveOffering($provider, $service);

        $dates = $this->bookableDates();
        $dateValues = array_column($dates, 'value');

        $selectedDate = $request->query('date');
        if (! in_array($selectedDate, $dateValues, true)) {
            $selectedDate = $dateValues[0];
        }

        $slots = $this->availableSlotsFor($provider, Carbon::parse($selectedDate));

        return view('consumer.bookings.create', compact(
            'provider', 'service', 'providerService', 'dates', 'selectedDate', 'slots'
        ));
    }

    public function store(Request $request, ProviderProfile $provider, Service $service): RedirectResponse
    {
        $providerService = $this->resolveOffering($provider, $service);

        if (! $this->geofence->isAllowed($provider->city)) {
            return back()->withInput()->with('error', 'Sorry, this provider is outside our current service areas.');
        }

        $dateValues = array_column($this->bookableDates(), 'value');

        $validated = $request->validate([
            'scheduled_date' => ['required', 'date', Rule::in($dateValues)],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $slots = collect($this->availableSlotsFor($provider, Carbon::parse($validated['scheduled_date'])));
        $slot = $slots->firstWhere('value', $validated['scheduled_time']);

        if (! $slot || ! $slot['available']) {
            return back()
                ->withInput()
                ->with('error', 'That time slot is no longer available. Please choose another.');
        }

        $booking = Booking::create([
            'reference' => $this->generateReference(),
            'consumer_id' => $request->user()->id,
            'corporate_account_id' => $request->user()->corporate_account_id,
            'provider_profile_id' => $provider->id,
            'service_id' => $service->id,
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'],
            'price' => $providerService->price,
            'duration_minutes' => $service->duration_minutes,
            'address' => $validated['address'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => Booking::STATUS_PENDING,
        ]);

        app(\App\Services\Notifier::class)->notify(
            $provider->user,
            'booking',
            'New booking request',
            'You have a new booking (' . $booking->reference . ') for ' . $service->name . '.',
            route('provider.bookings.show', $booking)
        );

        return redirect()
            ->route('consumer.bookings.show', $booking)
            ->with('success', 'Booking requested. The provider will confirm shortly.');
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['service.category', 'providerProfile.user', 'payments']);

        return view('consumer.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        if (! $booking->canBeCancelledByConsumer()) {
            return back()->with('error', 'This booking can no longer be cancelled.');
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

        return back()->with('success', 'Booking cancelled.' . ($payment && $payment->isRefunded() ? ' Your escrow payment has been refunded.' : ''));
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

    private function generateReference(): string
    {
        do {
            $ref = 'BK-' . strtoupper(Str::random(6));
        } while (Booking::where('reference', $ref)->exists());

        return $ref;
    }
}