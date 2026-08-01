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
    /** Body: reason (required, max 2000), photos[]? (up to 5 images, multipart) — evidence for the complaint. */
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
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:8192'],
        ]);

        $dispute = $booking->dispute()->create([
            'reference' => $this->generateReference(),
            'opened_by' => $user->id,
            'opened_by_role' => $booking->consumer_id === $user->id ? 'consumer' : 'provider',
            'reason' => $data['reason'],
            'status' => Dispute::STATUS_OPEN,
        ]);

        foreach ($request->file('photos', []) as $i => $photo) {
            $dispute->photos()->create([
                'path' => $photo->store('dispute-photos', 'public'),
                'original_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getClientMimeType(),
                'size' => $photo->getSize(),
                'sort_order' => $i,
            ]);
        }

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

        return response()->json(['dispute' => new DisputeResource($dispute->fresh('photos'))], 201);
    }

    public function show(Request $request, Dispute $dispute): JsonResponse
    {
        $dispute->load(['booking.service', 'booking.providerProfile.user', 'booking.consumer', 'opener', 'photos']);

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
