<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Dispute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function create(Request $request, Booking $booking): View
    {
        $user = $request->user();
        $booking->load(['service', 'providerProfile.user', 'consumer', 'dispute']);

        $this->authorize('dispute', $booking);
        abort_unless($booking->isDisputable(), 404);

        $backUrl = $this->backUrl($booking, $user);

        return view('disputes.create', compact('booking', 'backUrl'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $user = $request->user();
        $booking->load(['providerProfile', 'dispute']);

        $this->authorize('dispute', $booking);

        if (! $booking->isDisputable()) {
            return redirect($this->backUrl($booking, $user))
                ->with('error', 'A dispute cannot be opened for this booking.');
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
            route('disputes.show', $dispute)
        );

        return redirect()
            ->route('disputes.show', $dispute)
            ->with('success', 'Your dispute has been submitted. Our team will review it.');
    }

    public function show(Request $request, Dispute $dispute): View
    {
        $user = $request->user();
        $dispute->load(['booking.service', 'booking.providerProfile.user', 'booking.consumer', 'opener']);

        $this->authorize('view', $dispute);

        $backUrl = $this->backUrl($dispute->booking, $user);

        return view('disputes.show', compact('dispute', 'backUrl'));
    }

    private function backUrl(Booking $booking, $user): string
    {
        return $booking->isProviderUser($user)
            ? route('provider.bookings.show', $booking)
            : route('consumer.bookings.show', $booking);
    }

    private function generateReference(): string
    {
        do {
            $ref = 'DSP-' . strtoupper(Str::random(6));
        } while (Dispute::where('reference', $ref)->exists());

        return $ref;
    }
}