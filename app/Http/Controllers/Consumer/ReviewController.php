<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(Request $request, Booking $booking): View
    {
        $this->authorize('review', $booking);

        $booking->load(['service', 'providerProfile.user', 'review']);

        abort_unless($booking->isReviewable(), 404);

        return view('consumer.reviews.create', compact('booking'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('review', $booking);

        $booking->load(['providerProfile', 'review']);

        if (! $booking->isReviewable()) {
            return redirect()
                ->route('consumer.bookings.show', $booking)
                ->with('error', 'This booking can no longer be reviewed.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking->review()->create([
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

        return redirect()
            ->route('consumer.bookings.show', $booking)
            ->with('success', 'Thanks! Your review has been posted.');
    }
}