<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmergencyRequestResource;
use App\Models\Booking;
use App\Models\EmergencyRequest;
use App\Models\ProviderProfile;
use App\Services\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class EmergencyController extends Controller
{
    /** Open emergency requests in the provider's city, for services they offer. */
    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->providerProfile;

        if (! $profile || ! $profile->isApproved()) {
            return response()->json(['emergencies' => []]);
        }

        $offerings = $profile->providerServices()->where('is_active', true)->get();
        $serviceIds = $offerings->pluck('service_id');
        $prices = $offerings->pluck('price', 'service_id');

        $requests = EmergencyRequest::with(['service.category', 'consumer'])
            ->where('status', EmergencyRequest::STATUS_OPEN)
            ->whereIn('service_id', $serviceIds)
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($profile->city ?? ''))])
            ->latest()
            ->get();

        $requests->each(function (EmergencyRequest $r) use ($prices) {
            $r->setAttribute('my_price', isset($prices[$r->service_id]) ? (float) $prices[$r->service_id] : null);
        });

        return response()->json(['emergencies' => EmergencyRequestResource::collection($requests)]);
    }

    /** Claims an open emergency request, creating a confirmed booking. First provider to accept wins. */
    public function accept(Request $request, EmergencyRequest $emergencyRequest): JsonResponse
    {
        $profile = $this->approvedProfile($request);

        $offering = $profile->providerServices()
            ->where('service_id', $emergencyRequest->service_id)
            ->where('is_active', true)
            ->first();

        if (! $offering) {
            return response()->json(['message' => 'You don\'t currently offer this service.'], 422);
        }

        if (
            config('emergency.match_city_only', true)
            && mb_strtolower(trim($profile->city ?? '')) !== mb_strtolower(trim($emergencyRequest->city))
        ) {
            return response()->json(['message' => 'This emergency request is outside your city.'], 422);
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
            return response()->json(['message' => 'This request has already been taken by another provider.'], 409);
        }

        app(Notifier::class)->notify(
            $emergencyRequest->consumer,
            'booking',
            'A provider accepted your emergency request',
            'Help is on the way for ' . $emergencyRequest->reference . '. A confirmed booking (' . $booking->reference . ') has been created.',
            route('consumer.bookings.show', $booking)
        );

        return response()->json([
            'message' => 'You accepted the emergency request. A confirmed booking has been created.',
            'booking' => new \App\Http\Resources\BookingResource($booking->load(['service', 'consumer'])),
        ], 201);
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
