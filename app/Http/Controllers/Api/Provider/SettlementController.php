<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderSettlementResource;
use App\Models\ProviderSettlement;
use App\Services\Notifier;
use App\Services\ProviderSettlementService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * A provider paying back commission owed from cash bookings — the opposite
 * direction of a withdrawal. Submitting here doesn't touch the wallet;
 * only an admin confirming the amount actually received does (mirrors
 * Provider\SettlementController on the web).
 */
class SettlementController extends Controller
{
    public function __construct(
        private ProviderSettlementService $settlements,
        private WalletService $wallets,
    ) {}

    /** How much commission is owed (available_balance < 0) plus past settlement submissions. */
    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile, 404);

        $wallet = $this->wallets->walletFor($request->user());
        $owed = (float) $wallet->available_balance < 0 ? abs((float) $wallet->available_balance) : 0.0;

        $history = $profile->settlements()->latest()->paginate(15);

        return response()->json([
            'owed' => $owed,
            'is_suspended' => $profile->isSuspended(),
            'settlements' => ProviderSettlementResource::collection($history),
            'company_account' => config('payments.company_account'),
            'pagination' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'total' => $history->total(),
            ],
        ]);
    }

    /** Body: method (cash|bank_transfer), amount (<= owed), screenshot (required if bank_transfer). */
    public function store(Request $request): JsonResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile, 404);

        $wallet = $this->wallets->walletFor($request->user());
        $owed = (float) $wallet->available_balance < 0 ? abs((float) $wallet->available_balance) : 0.0;

        if ($owed <= 0) {
            return response()->json(['message' => 'You don\'t have any outstanding commission owed right now.'], 422);
        }

        $data = $request->validate([
            'method' => ['required', Rule::in([ProviderSettlement::METHOD_CASH, ProviderSettlement::METHOD_BANK_TRANSFER])],
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $owed],
            'screenshot' => [
                Rule::requiredIf($request->input('method') === ProviderSettlement::METHOD_BANK_TRANSFER),
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:8192',
            ],
        ]);

        $path = null;
        if ($request->hasFile('screenshot')) {
            $path = $request->file('screenshot')->store('settlement-screenshots', 'public');
        }

        $settlement = $this->settlements->submit($profile, $data['method'], (float) $data['amount'], $path);

        app(Notifier::class)->notifyAdmins(
            'settlement',
            'Commission settlement submitted',
            ($profile->business_name ?: $request->user()->name) . ' submitted Rs. ' . number_format($settlement->amount, 0) . ' via ' . $settlement->methodLabel() . '.',
            route('admin.settlements.show', $settlement)
        );

        return response()->json([
            'message' => 'Settlement submitted. We\'ll confirm it shortly.',
            'settlement' => new ProviderSettlementResource($settlement),
        ], 201);
    }
}
