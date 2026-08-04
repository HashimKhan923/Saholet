<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\EmergencyRequest;
use App\Models\ProviderProfile;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmergencyController extends Controller
{
    /** Status tabs the index supports, in display order. */
    private const FILTERS = ['all', 'open', 'quoted', 'accepted', 'matched', 'declined', 'cancelled'];

    public function index(Request $request): View
    {
        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, self::FILTERS, true)) {
            $filter = 'all';
        }

        $tally = EmergencyRequest::selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = ['all' => (int) $tally->sum()];
        foreach (self::FILTERS as $status) {
            if ($status !== 'all') {
                $counts[$status] = (int) ($tally[$status] ?? 0);
            }
        }

        $query = EmergencyRequest::with(['service.category', 'consumer']);

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        $emergencies = $query
            ->orderByRaw("CASE WHEN status IN ('open','quoted','accepted') THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.emergencies.index', compact('emergencies', 'counts', 'filter'));
    }

    public function show(EmergencyRequest $emergencyRequest): View
    {
        $emergencyRequest->load(['service.category', 'consumer', 'quotedBy', 'matchedProvider.user', 'booking']);

        $candidateProviders = collect();

        if ($emergencyRequest->isAccepted()) {
            $candidateProviders = ProviderProfile::approved()
                ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($emergencyRequest->city))])
                ->whereHas('providerServices', fn ($q) => $q->where('service_id', $emergencyRequest->service_id)->where('is_active', true))
                ->with('user')
                ->orderByDesc('rating_avg')
                ->orderByDesc('reviews_count')
                ->get();
        }

        return view('admin.emergencies.show', compact('emergencyRequest', 'candidateProviders'));
    }

    public function quote(Request $request, EmergencyRequest $emergencyRequest): RedirectResponse
    {
        if (! $emergencyRequest->isOpen()) {
            return back()->with('error', 'Only a newly submitted request can be quoted.');
        }

        $data = $request->validate([
            'quoted_price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $emergencyRequest->update([
            'status' => EmergencyRequest::STATUS_QUOTED,
            'quoted_price' => $data['quoted_price'],
            'admin_note' => $data['admin_note'] ?? null,
            'quoted_at' => now(),
            'quoted_by' => $request->user()->id,
        ]);

        app(Notifier::class)->notify(
            $emergencyRequest->consumer,
            'emergency',
            'Price ready for your emergency request',
            'We quoted Rs. ' . number_format((float) $data['quoted_price'], 0) . ' for ' . $emergencyRequest->reference . '. Please review and accept to proceed.',
            route('consumer.emergencies.show', $emergencyRequest)
        );

        return redirect()->route('admin.emergencies.show', $emergencyRequest)->with('success', 'Quote sent to the customer.');
    }

    public function assign(Request $request, EmergencyRequest $emergencyRequest): RedirectResponse
    {
        if (! $emergencyRequest->isAccepted()) {
            return back()->with('error', 'The customer must accept the quote before a provider can be assigned.');
        }

        $data = $request->validate([
            'provider_profile_id' => ['required', 'exists:provider_profiles,id'],
        ]);

        $provider = ProviderProfile::approved()->findOrFail($data['provider_profile_id']);

        $emergencyRequest->loadMissing('service');

        $booking = DB::transaction(function () use ($emergencyRequest, $provider) {
            $fresh = EmergencyRequest::whereKey($emergencyRequest->id)->lockForUpdate()->first();

            if (! $fresh || ! $fresh->isAccepted()) {
                return null;
            }

            $booking = Booking::create([
                'reference' => $this->generateBookingReference(),
                'consumer_id' => $fresh->consumer_id,
                'provider_profile_id' => $provider->id,
                'service_id' => $fresh->service_id,
                'scheduled_date' => now()->toDateString(),
                'scheduled_time' => now()->format('H:i'),
                'price' => $fresh->quoted_price,
                'duration_minutes' => $emergencyRequest->service->duration_minutes,
                'address' => $fresh->address,
                'latitude' => $fresh->latitude,
                'longitude' => $fresh->longitude,
                'notes' => $fresh->notes ?: ('Emergency request ' . $fresh->reference),
                'status' => Booking::STATUS_CONFIRMED,
                'confirmed_at' => now(),
                'source' => 'emergency',
            ]);

            $fresh->update([
                'status' => EmergencyRequest::STATUS_MATCHED,
                'booking_id' => $booking->id,
                'matched_provider_profile_id' => $provider->id,
                'matched_at' => now(),
            ]);

            return $booking;
        });

        if (! $booking) {
            return back()->with('error', 'This request is no longer awaiting assignment.');
        }

        $notifier = app(Notifier::class);
        $notifier->notify(
            $provider->user,
            'booking',
            'Emergency booking assigned',
            'You’ve been assigned an emergency booking (' . $booking->reference . ').',
            route('provider.bookings.show', $booking)
        );
        $notifier->notify(
            $emergencyRequest->consumer,
            'booking',
            'Provider assigned',
            'A provider has been assigned to ' . $emergencyRequest->reference . '. Booking ' . $booking->reference . ' is confirmed.',
            route('consumer.bookings.show', $booking)
        );

        return redirect()->route('admin.bookings.show', $booking)->with('success', 'Provider assigned. Booking created.');
    }

    private function generateBookingReference(): string
    {
        do {
            $ref = 'BK-' . strtoupper(Str::random(6));
        } while (Booking::where('reference', $ref)->exists());

        return $ref;
    }
}
