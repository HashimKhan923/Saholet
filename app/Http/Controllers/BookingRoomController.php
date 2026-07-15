<?php

namespace App\Http\Controllers;

use App\Events\LocationUpdated;
use App\Events\MessageSent;
use App\Models\Booking;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingRoomController extends Controller
{
    public function show(Request $request, Booking $booking): View
    {
        $user = $request->user();
        $booking->load(['providerProfile.user', 'consumer', 'service']);

        $this->authorize('view', $booking);

        $isProvider = $booking->isProviderUser($user);

        $messages = $booking->messages()
            ->with('sender')
            ->orderBy('id')
            ->get()
            ->map(fn (Message $m) => [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender->name,
                'body' => $m->body,
                'created_at' => $m->created_at->format('d M, g:i A'),
            ])
            ->values();

        $latest = $booking->trackingUpdates()->latest('id')->first();

        $tracking = $latest ? [
            'latitude' => (float) $latest->latitude,
            'longitude' => (float) $latest->longitude,
            'note' => $latest->note,
            'time' => $latest->created_at->format('d M, g:i A'),
        ] : null;

        $otherParty = $isProvider
            ? $booking->consumer->name
            : ($booking->providerProfile->business_name ?: $booking->providerProfile->user->name);

        $backUrl = $isProvider
            ? route('provider.bookings.show', $booking)
            : route('consumer.bookings.show', $booking);

        return view('booking.room', compact(
            'booking', 'isProvider', 'messages', 'tracking', 'otherParty', 'backUrl'
        ));
    }

    public function sendMessage(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();
        $booking->load('providerProfile');

        $this->authorize('view', $booking);

        if (! $booking->isCommunicable()) {
            return response()->json(['message' => 'This conversation is closed.'], 422);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $booking->messages()->create([
            'sender_id' => $user->id,
            'body' => $data['body'],
        ]);
        $message->load('sender');

        try {
            event(new MessageSent($message));
        } catch (\Throwable $e) {
            report($e); // best-effort: realtime server may be offline
        }

        return response()->json([
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender->name,
            'body' => $message->body,
            'created_at' => $message->created_at->format('d M, g:i A'),
        ], 201);
    }

    public function shareLocation(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();
        $booking->load('providerProfile');

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

        return response()->json([
            'latitude' => (float) $update->latitude,
            'longitude' => (float) $update->longitude,
            'note' => $update->note,
            'time' => $update->created_at->format('d M, g:i A'),
        ], 201);
    }
}