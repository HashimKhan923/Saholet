<?php

namespace App\Http\Controllers\Api;

use App\Events\LocationUpdated;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\TrackingUpdateResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingRoomController extends Controller
{
    /** Chat history + latest tracking pin for a booking. */
    public function show(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        $booking->load(['providerProfile.user', 'consumer', 'service']);

        $messages = $booking->messages()->with('sender')->orderBy('id')->get();
        $tracking = $booking->trackingUpdates()->latest('id')->first();

        return response()->json([
            'is_communicable' => $booking->isCommunicable(),
            'can_share_location' => $booking->canShareLocation(),
            'messages' => MessageResource::collection($messages),
            'latest_tracking' => $tracking ? new TrackingUpdateResource($tracking) : null,
        ]);
    }

    public function sendMessage(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        if (! $booking->isCommunicable()) {
            return response()->json(['message' => 'This conversation is closed.'], 422);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $booking->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        $message->load('sender');

        try {
            event(new MessageSent($message));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['message_data' => new MessageResource($message)], 201);
    }

    /** Provider (or whoever the policy allows) posts a live location pin. */
    public function shareLocation(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('shareLocation', $booking);

        if (! $booking->canShareLocation()) {
            return response()->json(['message' => 'Location sharing is not available for this booking.'], 422);
        }

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $update = $booking->trackingUpdates()->create($data);

        try {
            event(new LocationUpdated($update));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['tracking' => new TrackingUpdateResource($update)], 201);
    }
}
