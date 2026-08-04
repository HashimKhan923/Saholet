<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private WalletService $wallets, private InvoiceService $invoices) {}

/** Status tabs the index supports, in display order. */
    private const FILTERS = ['all', 'pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];

    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, self::FILTERS, true)) {
            $filter = 'all';
        }

        $search = trim((string) $request->query('q', ''));

        // No profile yet → render an empty paginator so the view stays uniform.
        if (! $profile) {
            return view('provider.bookings.index', [
                'bookings' => Booking::whereRaw('1 = 0')->paginate(12),
                'counts'   => array_fill_keys(self::FILTERS, 0),
                'filter'   => $filter,
                'search'   => $search,
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

        $query = Booking::with(['service', 'consumer', 'review'])
            ->where('provider_profile_id', $profile->id);

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('consumer', fn (Builder $c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('service', fn (Builder $s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        // Pending first (they need action), then most recent.
        $bookings = $query
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('provider.bookings.index', compact('bookings', 'counts', 'filter', 'search'));
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['service.category', 'consumer', 'payments', 'review', 'dispute', 'completionPhotos']);

        return view('provider.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('updateStatus', $booking);

        $isPostInspectionCancel = $request->input('action') === 'cancel' && $booking->isInProgress();

        $data = $request->validate([
            'action' => ['required', Rule::in(['confirm', 'decline', 'start', 'complete', 'cancel'])],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
            'completion_notes' => ['nullable', 'string', 'max:1000'],
            'before_photos' => ['nullable', 'array', 'max:6'],
            'before_photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'after_photos' => ['nullable', 'array', 'max:6'],
            'after_photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'visit_charge_method' => [Rule::requiredIf($isPostInspectionCancel), 'nullable', Rule::in(['cash', 'bank_transfer'])],
            'visit_charge_screenshot' => [
                Rule::requiredIf($isPostInspectionCancel && $request->input('visit_charge_method') === 'bank_transfer'),
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:8192',
            ],
        ], [
            'visit_charge_method.required' => 'Let us know how the visit charge was paid.',
            'visit_charge_screenshot.required' => 'Add a screenshot of the bank transfer as proof.',
        ]);

        $action = $data['action'];

        $allowed = match ($action) {
            'confirm', 'decline' => $booking->isPending(),
            'start' => $booking->isConfirmed(),
            'cancel' => $booking->isConfirmed() || $booking->isInProgress(),
            'complete' => $booking->isInProgress(),
            default => false,
        };

        if (! $allowed) {
            return back()->with('error', 'That action is not allowed for this booking right now.');
        }

        if ($action === 'confirm' && $booking->providerProfile->isSuspended()) {
            return back()->with('error', 'Your account is paused for an unpaid commission balance — settle it before accepting new bookings.');
        }

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
                $booking->update([
                    'status' => Booking::STATUS_COMPLETED,
                    'completed_at' => now(),
                    'completion_notes' => $data['completion_notes'] ?? null,
                ]);
                $this->storeCompletionPhotos($booking, $request->file('before_photos', []), 'before');
                $this->storeCompletionPhotos($booking, $request->file('after_photos', []), 'after');
                $this->invoices->createForBooking($booking);
                $message = 'Marked as completed.';

                // Optional auto-release mode.
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

                $update = [
                    'status' => Booking::STATUS_CANCELLED,
                    'cancelled_by' => 'provider',
                    'cancelled_at' => now(),
                    'cancellation_reason' => ($data['cancellation_reason'] ?? null)
                        ?: ($action === 'decline' ? 'Declined by provider' : 'Cancelled by provider'),
                ];

                if ($isPostInspectionCancel) {
                    $update['visit_charge_amount'] = $booking->service->visit_charge;
                    $update['visit_charge_method'] = $data['visit_charge_method'];
                    $update['visit_charge_collected_at'] = now();
                    if ($request->hasFile('visit_charge_screenshot')) {
                        $update['visit_charge_screenshot_path'] = $request->file('visit_charge_screenshot')->store('visit-charge-screenshots', 'public');
                    }
                }

                $booking->update($update);
                $message = $action === 'decline'
                    ? 'Booking declined.'
                    : ($isPostInspectionCancel ? 'Booking cancelled — visit charge recorded.' : 'Booking cancelled.');
                break;
        }

        app(\App\Services\Notifier::class)->notify(
            $booking->consumer,
            'booking',
            'Booking ' . $booking->reference . ' updated',
            'Your booking is now ' . str_replace('_', ' ', $booking->status) . '.',
            route('consumer.bookings.show', $booking)
        );

        if ($action === 'complete') {
            app(\App\Services\Notifier::class)->notify(
                $booking->consumer,
                'booking',
                'Your invoice is ready',
                'The invoice for booking ' . $booking->reference . ' is ready to view or download.',
                route('bookings.receipt', $booking)
            );
        }

        return back()->with('success', $message);
    }

    /** @param array<int, \Illuminate\Http\UploadedFile> $photos */
    private function storeCompletionPhotos(Booking $booking, array $photos, string $type): void
    {
        if (empty($photos)) {
            return;
        }

        $nextSort = (int) ($booking->completionPhotos()->where('type', $type)->max('sort_order') ?? 0);

        foreach ($photos as $photo) {
            $path = $photo->store("bookings/{$booking->id}/completion", 'public');

            $booking->completionPhotos()->create([
                'type' => $type,
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getClientMimeType(),
                'size' => $photo->getSize(),
                'sort_order' => ++$nextSort,
            ]);
        }
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