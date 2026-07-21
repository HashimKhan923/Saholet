<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    private const FILTERS = ['all', 'pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];

    /** Query: status (one of the filters above, default all), q (search reference/address/consumer/service). */
    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->providerProfile;

        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, self::FILTERS, true)) {
            $filter = 'all';
        }

        $search = trim((string) $request->query('q', ''));

        if (! $profile) {
            return response()->json([
                'bookings' => [],
                'counts' => array_fill_keys(self::FILTERS, 0),
                'filter' => $filter,
            ]);
        }

        $tally = Booking::where('provider_profile_id', $profile->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = ['all' => (int) $tally->sum()];
        foreach (self::FILTERS as $status) {
            if ($status !== 'all') {
                $counts[$status] = (int) ($tally[$status] ?? 0);
            }
        }

        $query = Booking::with(['service', 'consumer'])->where('provider_profile_id', $profile->id);

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('consumer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('service', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        $bookings = $query
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return response()->json([
            'bookings' => BookingResource::collection($bookings),
            'counts' => $counts,
            'filter' => $filter,
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        $booking->load(['service.category', 'consumer', 'payments', 'review', 'dispute']);

        return response()->json(['booking' => new BookingResource($booking)]);
    }

    /** Body: action (confirm|decline|start|complete|cancel), cancellation_reason? (for decline/cancel). */
    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('updateStatus', $booking);

        $data = $request->validate([
            'action' => ['required', Rule::in(['confirm', 'decline', 'start', 'complete', 'cancel'])],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $action = $data['action'];

        $allowed = match ($action) {
            'confirm', 'decline' => $booking->isPending(),
            'start', 'cancel' => $booking->isConfirmed(),
            'complete' => $booking->isInProgress(),
            default => false,
        };

        if (! $allowed) {
            return response()->json(['message' => 'That action is not allowed for this booking right now.'], 422);
        }

        $message = '';

        switch ($action) {
            case 'confirm':
                $booking->update(['status' => Booking::STATUS_CONFIRMED, 'confirmed_at' => now()]);
                $message = 'Booking confirmed.';
                break;

            case 'start':
                $booking->update(['status' => Booking::STATUS_IN_PROGRESS, 'started_at' => now()]);
                $message = 'Marked as in progress.';
                break;

            case 'complete':
                $booking->update(['status' => Booking::STATUS_COMPLETED, 'completed_at' => now()]);
                $message = 'Marked as completed.';

                if (config('payments.release_mode') === 'auto_on_complete') {
                    $booking->load(['providerProfile.user', 'payments']);
                    $payment = $booking->activePayment();
                    if ($payment && $payment->isEscrow()) {
                        $this->wallets->release($payment, $booking->providerProfile->user);
                        $message = 'Marked as completed. Escrow released to your wallet.';
                    }
                }
                break;

            case 'decline':
            case 'cancel':
                $this->refundIfPaid($booking);

                $booking->update([
                    'status' => Booking::STATUS_CANCELLED,
                    'cancelled_by' => 'provider',
                    'cancelled_at' => now(),
                    'cancellation_reason' => $data['cancellation_reason']
                        ?: ($action === 'decline' ? 'Declined by provider' : 'Cancelled by provider'),
                ]);
                $message = $action === 'decline' ? 'Booking declined.' : 'Booking cancelled.';
                break;
        }

        app(\App\Services\Notifier::class)->notify(
            $booking->consumer,
            'booking',
            'Booking ' . $booking->reference . ' updated',
            'Your booking is now ' . str_replace('_', ' ', $booking->status) . '.',
            route('consumer.bookings.show', $booking)
        );

        return response()->json([
            'message' => $message,
            'booking' => new BookingResource($booking->fresh(['service', 'consumer', 'payments'])),
        ]);
    }

    private function refundIfPaid(Booking $booking): void
    {
        $booking->load(['providerProfile.user', 'payments']);
        $payment = $booking->activePayment();

        if ($payment && $payment->isEscrow()) {
            $this->wallets->refund($payment, $booking->providerProfile->user);
        }
    }
}
