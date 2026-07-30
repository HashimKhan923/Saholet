<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ProviderSettlement;
use App\Services\Notifier;
use App\Services\ProviderSettlementService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * A provider paying back commission owed from cash bookings — the opposite
 * direction of a withdrawal. Submitting here doesn't touch the wallet;
 * only an admin confirming the amount actually received does.
 */
class SettlementController extends Controller
{
    public function __construct(
        private ProviderSettlementService $settlements,
        private WalletService $wallets,
    ) {}

    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile, 404);

        $wallet = $this->wallets->walletFor($request->user());
        $history = $profile->settlements()->latest()->paginate(10);

        return view('provider.settlements.index', compact('wallet', 'history'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;
        abort_unless($profile, 404);

        $wallet = $this->wallets->walletFor($request->user());
        $owed = (float) $wallet->available_balance < 0 ? abs((float) $wallet->available_balance) : 0.0;

        if ($owed <= 0) {
            return back()->with('error', 'You don\'t have any outstanding commission owed right now.');
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

        return redirect()
            ->route('provider.settlements.index')
            ->with('success', 'Settlement submitted. We\'ll confirm it shortly.');
    }
}
