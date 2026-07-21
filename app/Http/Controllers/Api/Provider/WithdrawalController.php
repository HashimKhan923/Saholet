<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\WithdrawalRequestResource;
use App\Services\Notifier;
use App\Services\WalletService;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function __construct(
        private WithdrawalService $withdrawals,
        private WalletService $wallets,
    ) {}

    /** Body: amount (>= config min_withdrawal, <= available_balance). Requires a saved payout method. */
    public function store(Request $request): JsonResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile, 404);

        if (! $profile->hasPayoutMethod()) {
            return response()->json(['message' => 'Add your payout details before requesting a withdrawal.'], 422);
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

        return response()->json([
            'message' => 'Withdrawal requested. We\'ll process it and mark it paid once the transfer is sent.',
            'withdrawal' => new WithdrawalRequestResource($withdrawal),
        ], 201);
    }
}
