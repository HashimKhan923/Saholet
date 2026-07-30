<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\Notifier;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function __construct(private WithdrawalService $withdrawals) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $validStatuses = ['all', 'pending', 'awaiting_confirmation', 'paid', 'rejected'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'all';
        }

        $query = WithdrawalRequest::with('providerProfile.user')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('admin.withdrawals.index', compact('requests', 'status'));
    }

    public function show(WithdrawalRequest $withdrawal): View
    {
        $withdrawal->load('providerProfile.user', 'processor');

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function markPaidCash(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        if (! $withdrawal->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $this->withdrawals->markPaidCash($withdrawal, $request->user());

        app(Notifier::class)->notify(
            $withdrawal->providerProfile->user,
            'withdrawal',
            'Withdrawal paid',
            'Your withdrawal of Rs. ' . number_format($withdrawal->amount, 0) . ' (' . $withdrawal->reference . ') was handed to you in cash.',
            route('provider.wallet.index')
        );

        return back()->with('success', 'Marked as paid.');
    }

    public function markPaidBankTransfer(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        if (! $withdrawal->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $data = $request->validate([
            'screenshot' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:8192'],
        ]);

        $path = $request->file('screenshot')->store('withdrawal-screenshots', 'public');

        $this->withdrawals->markPaidBankTransfer($withdrawal, $request->user(), $path);

        app(Notifier::class)->notify(
            $withdrawal->providerProfile->user,
            'withdrawal',
            'Withdrawal sent — please confirm',
            'Rs. ' . number_format($withdrawal->amount, 0) . ' (' . $withdrawal->reference . ') was sent via bank transfer. Please confirm you received it.',
            route('provider.wallet.index')
        );

        return back()->with('success', 'Marked as sent. Waiting on the provider to confirm receipt.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        if (! $withdrawal->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->withdrawals->reject($withdrawal, $request->user(), $data['admin_notes'] ?? null);

        app(Notifier::class)->notify(
            $withdrawal->providerProfile->user,
            'withdrawal',
            'Withdrawal request rejected',
            'Your withdrawal request (' . $withdrawal->reference . ') was rejected and the funds were returned to your available balance.',
            route('provider.wallet.index')
        );

        return back()->with('success', 'Request rejected and funds returned to the provider.');
    }
}
