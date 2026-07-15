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
        $status = $request->query('status', 'pending');

        $validStatuses = ['pending', 'paid', 'rejected', 'all'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'pending';
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

    public function markPaid(Request $request, WithdrawalRequest $withdrawal): RedirectResponse
    {
        if (! $withdrawal->isPending()) {
            return back()->with('error', 'This request has already been processed.');
        }

        $this->withdrawals->markPaid($withdrawal, $request->user());

        app(Notifier::class)->notify(
            $withdrawal->providerProfile->user,
            'withdrawal',
            'Withdrawal paid',
            'Your withdrawal of Rs. ' . number_format($withdrawal->amount, 0) . ' (' . $withdrawal->reference . ') has been sent.',
            route('provider.wallet.index')
        );

        return back()->with('success', 'Marked as paid.');
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
