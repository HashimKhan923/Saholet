<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Services\Notifier;
use App\Services\WalletService;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function __construct(
        private WithdrawalService $withdrawals,
        private WalletService $wallets,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile, 404);

        if (! $profile->hasPayoutMethod()) {
            return back()->with('error', 'Add your payout details before requesting a withdrawal.');
        }

        $wallet = $this->wallets->walletFor($request->user());
        $min = (float) config('payments.min_withdrawal');

        $data = $request->validate([
            'amount' => ['required', 'numeric', "min:{$min}", 'max:' . (float) $wallet->available_balance],
        ]);

        $withdrawal = $this->withdrawals->request($profile, (float) $data['amount']);

        app(Notifier::class)->notifyAdmins(
            'withdrawal',
            'New withdrawal request',
            $profile->business_name . ' requested Rs. ' . number_format($withdrawal->amount, 0) . ' — ' . $withdrawal->methodLabel() . '.',
            route('admin.withdrawals.show', $withdrawal)
        );

        return back()->with('success', 'Withdrawal requested. We\'ll process it and mark it paid once the transfer is sent.');
    }
}
