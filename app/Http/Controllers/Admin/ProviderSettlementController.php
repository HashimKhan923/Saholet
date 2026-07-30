<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderSettlement;
use App\Services\Notifier;
use App\Services\ProviderSettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderSettlementController extends Controller
{
    public function __construct(private ProviderSettlementService $settlements) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');

        $validStatuses = ['all', 'pending', 'confirmed', 'rejected'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'all';
        }

        $query = ProviderSettlement::with('providerProfile.user')->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $settlements = $query->paginate(15)->withQueryString();

        $counts = ['pending' => ProviderSettlement::where('status', ProviderSettlement::STATUS_PENDING)->count()];

        return view('admin.settlements.index', compact('settlements', 'status', 'counts'));
    }

    public function show(ProviderSettlement $settlement): View
    {
        $settlement->load(['providerProfile.user', 'confirmedBy', 'wallet']);

        return view('admin.settlements.show', compact('settlement'));
    }

    public function confirm(Request $request, ProviderSettlement $settlement): RedirectResponse
    {
        if (! $settlement->isPending()) {
            return back()->with('error', 'This settlement has already been processed.');
        }

        $data = $request->validate([
            'confirmed_amount' => ['required', 'numeric', 'min:0.01', 'max:' . (float) $settlement->amount],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->settlements->confirm($settlement, (float) $data['confirmed_amount'], $request->user(), $data['admin_notes'] ?? null);

        app(Notifier::class)->notify(
            $settlement->providerProfile->user,
            'settlement',
            'Settlement confirmed',
            'Rs. ' . number_format((float) $data['confirmed_amount'], 0) . ' has been applied to your wallet.',
            route('provider.settlements.index')
        );

        return back()->with('success', 'Settlement confirmed and applied to the provider\'s wallet.');
    }

    public function reject(Request $request, ProviderSettlement $settlement): RedirectResponse
    {
        if (! $settlement->isPending()) {
            return back()->with('error', 'This settlement has already been processed.');
        }

        $data = $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $this->settlements->reject($settlement, $request->user(), $data['admin_notes']);

        app(Notifier::class)->notify(
            $settlement->providerProfile->user,
            'settlement',
            'Settlement rejected',
            $data['admin_notes'],
            route('provider.settlements.index')
        );

        return back()->with('success', 'Settlement rejected.');
    }
}
