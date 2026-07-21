<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DisputeResource;
use App\Models\Booking;
use App\Models\Dispute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DisputeController extends Controller
{
    public function store(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();
        $booking->load(['providerProfile', 'dispute']);

        $this->authorize('dispute', $booking);

        if (! $booking->isDisputable()) {
            return response()->json(['message' => 'A dispute cannot be opened for this booking.'], 422);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $dispute = $booking->dispute()->create([
            'reference' => $this->generateReference(),
            'opened_by' => $user->id,
            'opened_by_role' => $booking->consumer_id === $user->id ? 'consumer' : 'provider',
            'reason' => $data['reason'],
            'status' => Dispute::STATUS_OPEN,
        ]);

        $otherParty = $booking->consumer_id === $user->id
            ? $booking->providerProfile->user
            : $booking->consumer;

        app(\App\Services\Notifier::class)->notify(
            $otherParty,
            'dispute',
            'A dispute was opened',
            'A dispute (' . $dispute->reference . ') was opened on booking ' . $booking->reference . '.',
            route('provider.bookings.show', $booking)
        );

        return response()->json(['dispute' => new DisputeResource($dispute)], 201);
    }

    public function show(Request $request, Dispute $dispute): JsonResponse
    {
        $dispute->load(['booking.service', 'booking.providerProfile.user', 'booking.consumer', 'opener']);

        $this->authorize('view', $dispute);

        return response()->json(['dispute' => new DisputeResource($dispute)]);
    }

    private function generateReference(): string
    {
        do {
            $ref = 'DSP-' . strtoupper(Str::random(6));
        } while (Dispute::where('reference', $ref)->exists());

        return $ref;
    }
}
