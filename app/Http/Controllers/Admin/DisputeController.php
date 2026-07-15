<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', 'open');

        if (! in_array($status, ['open', 'resolved', 'dismissed', 'all'], true)) {
            $status = 'open';
        }

        $query = Dispute::with(['booking.service', 'booking.consumer', 'booking.providerProfile.user', 'opener'])
            ->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $disputes = $query->paginate(15)->withQueryString();

        $counts = [
            'open' => Dispute::where('status', Dispute::STATUS_OPEN)->count(),
            'resolved' => Dispute::where('status', Dispute::STATUS_RESOLVED)->count(),
            'dismissed' => Dispute::where('status', Dispute::STATUS_DISMISSED)->count(),
        ];

        return view('admin.disputes.index', compact('disputes', 'status', 'counts'));
    }

    public function show(Dispute $dispute): View
    {
        $dispute->load([
            'booking.service.category',
            'booking.consumer',
            'booking.providerProfile.user',
            'booking.payments',
            'opener',
            'resolver',
        ]);

        return view('admin.disputes.show', compact('dispute'));
    }

    public function resolve(Request $request, Dispute $dispute): RedirectResponse
    {
        if (! $dispute->isOpen()) {
            return back()->with('error', 'This dispute has already been closed.');
        }

        $data = $request->validate([
            'resolution' => ['required', Rule::in(['release', 'refund', 'dismiss'])],
            'resolution_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $dispute->load(['booking.providerProfile.user', 'booking.payments']);
        $booking = $dispute->booking;
        $payment = $booking->activePayment();
        $moneyMoved = false;

        if ($data['resolution'] === 'release') {
            if ($payment && $payment->isEscrow()) {
                $this->wallets->release($payment, $booking->providerProfile->user);
                $moneyMoved = true;
            }
            $dispute->update([
                'status' => Dispute::STATUS_RESOLVED,
                'resolution' => Dispute::RESOLUTION_RELEASE,
                'resolution_note' => $data['resolution_note'] ?? null,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);
            $message = $moneyMoved ? 'Dispute resolved — escrow released to the provider.' : 'Dispute resolved in the provider’s favour.';
        } elseif ($data['resolution'] === 'refund') {
            if ($payment && $payment->isEscrow()) {
                $this->wallets->refund($payment, $booking->providerProfile->user);
                $moneyMoved = true;
            }
            $dispute->update([
                'status' => Dispute::STATUS_RESOLVED,
                'resolution' => Dispute::RESOLUTION_REFUND,
                'resolution_note' => $data['resolution_note'] ?? null,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);
            $message = $moneyMoved ? 'Dispute resolved — escrow refunded to the customer.' : 'Dispute resolved in the customer’s favour.';
        } else {
            $dispute->update([
                'status' => Dispute::STATUS_DISMISSED,
                'resolution' => null,
                'resolution_note' => $data['resolution_note'] ?? null,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);
            $message = 'Dispute dismissed.';
        }

        $booking->loadMissing(['consumer', 'providerProfile.user']);
        $notifier = app(\App\Services\Notifier::class);

        foreach (array_filter([$booking->consumer, $booking->providerProfile?->user]) as $party) {
            $notifier->notify(
                $party,
                'dispute',
                'Dispute ' . $dispute->reference . ' closed',
                'The dispute on booking ' . $booking->reference . ' has been ' . $dispute->status . '.',
                route('disputes.show', $dispute)
            );
        }

        return back()->with('success', $message);
    }
}