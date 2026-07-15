<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\EmergencyRequest;
use App\Models\ProviderProfile;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmergencyController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        if (! $profile || ! $profile->isApproved()) {
            return view('provider.emergencies.index', [
                'approved' => false,
                'requests' => collect(),
                'myCity' => $profile?->city ?? '',
                'profileId' => $profile?->id ?? 0,
                'myServiceIds' => [],
                'myPrices' => [],
            ]);
        }

        $offerings = $profile->providerServices()->where('is_active', true)->get();
        $serviceIds = $offerings->pluck('service_id');

        $requests = EmergencyRequest::with(['service.category', 'consumer'])
            ->where('status', EmergencyRequest::STATUS_OPEN)
            ->whereIn('service_id', $serviceIds)
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($profile->city ?? ''))])
            ->latest()
            ->get();

        return view('provider.emergencies.index', [
            'approved' => true,
            'requests' => $requests,
            'myCity' => $profile->city ?? '',
            'profileId' => $profile->id,
            'myServiceIds' => $serviceIds->values(),
            'myPrices' => $offerings->pluck('price', 'service_id'),
        ]);
    }

    public function accept(Request $request, EmergencyRequest $emergencyRequest): RedirectResponse
    {
        $profile = $this->approvedProfile($request);

        $offering = $profile->providerServices()
            ->where('service_id', $emergencyRequest->service_id)
            ->where('is_active', true)
            ->first();

        if (! $offering) {
            return back()->with('error', 'You don’t currently offer this service.');
        }

        if (
            config('emergency.match_city_only', true)
            && mb_strtolower(trim($profile->city ?? '')) !== mb_strtolower(trim($emergencyRequest->city))
        ) {
            return back()->with('error', 'This emergency request is outside your city.');
        }

        $emergencyRequest->loadMissing('service');

        $booking = DB::transaction(function () use ($emergencyRequest, $profile, $offering) {
            $fresh = EmergencyRequest::whereKey($emergencyRequest->id)->lockForUpdate()->first();

            if (! $fresh || ! $fresh->isOpen()) {
                return null;
            }

            $booking = Booking::create([
                'reference' => $this->generateBookingReference(),
                'consumer_id' => $fresh->consumer_id,
                'provider_profile_id' => $profile->id,
                'service_id' => $fresh->service_id,
                'scheduled_date' => now()->toDateString(),
                'scheduled_time' => now()->format('H:i'),
                'price' => $offering->price,
                'duration_minutes' => $emergencyRequest->service->duration_minutes,
                'address' => $fresh->address,
                'notes' => $fresh->notes ?: ('Emergency request ' . $fresh->reference),
                'status' => Booking::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);

            $fresh->update([
                'status' => EmergencyRequest::STATUS_MATCHED,
                'booking_id' => $booking->id,
                'matched_provider_profile_id' => $profile->id,
                'matched_at' => now(),
            ]);

            return $booking;
        });

        if (! $booking) {
            return back()->with('error', 'This request has already been taken by another provider.');
        }

        app(Notifier::class)->notify(
            $emergencyRequest->consumer,
            'booking',
            'A provider accepted your emergency request',
            'Help is on the way for ' . $emergencyRequest->reference . '. A confirmed booking (' . $booking->reference . ') has been created.',
            route('consumer.bookings.show', $booking)
        );

        return redirect()
            ->route('provider.bookings.show', $booking)
            ->with('success', 'You accepted the emergency request. A confirmed booking has been created.');
    }

    private function approvedProfile(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }

    private function generateBookingReference(): string
    {
        do {
            $ref = 'BK-' . strtoupper(Str::random(6));
        } while (Booking::where('reference', $ref)->exists());

        return $ref;
    }
}