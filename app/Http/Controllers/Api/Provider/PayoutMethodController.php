<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderProfileResource;
use App\Models\WithdrawalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PayoutMethodController extends Controller
{
    /** Body: payout_method (bank|jazzcash|easypaisa), payout_account_title, payout_account_number, payout_bank_name (required if bank). */
    public function update(Request $request): JsonResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile, 404);

        $data = $request->validate([
            'payout_method' => ['required', Rule::in([
                WithdrawalRequest::METHOD_BANK,
                WithdrawalRequest::METHOD_JAZZCASH,
                WithdrawalRequest::METHOD_EASYPAISA,
            ])],
            'payout_account_title' => ['required', 'string', 'max:255'],
            'payout_account_number' => ['required', 'string', 'max:64'],
            'payout_bank_name' => ['required_if:payout_method,bank', 'nullable', 'string', 'max:255'],
        ]);

        $profile->update([
            'payout_method' => $data['payout_method'],
            'payout_account_title' => $data['payout_account_title'],
            'payout_account_number' => $data['payout_account_number'],
            'payout_bank_name' => $data['payout_method'] === WithdrawalRequest::METHOD_BANK ? $data['payout_bank_name'] : null,
        ]);

        return response()->json(['profile' => new ProviderProfileResource($profile->fresh())]);
    }
}
