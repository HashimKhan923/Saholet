<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('review', $booking);

        $booking->load(['providerProfile', 'review']);

        if (! $booking->isReviewable()) {
            return response()->json(['message' => 'This booking can no longer be reviewed.'], 422);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = $booking->review()->create([
            'consumer_id' => $request->user()->id,
            'provider_profile_id' => $booking->provider_profile_id,
            'service_id' => $booking->service_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        $booking->providerProfile->recomputeRating();

        app(\App\Services\Notifier::class)->notify(
            $booking->providerProfile->user,
            'review',
            'You received a new review',
            'A customer left a review on booking ' . $booking->reference . '.',
            route('provider.bookings.show', $booking)
        );

        return response()->json(['review' => new ReviewResource($review)], 201);
    }
}
