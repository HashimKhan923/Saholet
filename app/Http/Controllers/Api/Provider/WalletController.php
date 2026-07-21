<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\LedgerEntryResource;
use App\Http\Resources\WalletResource;
use App\Http\Resources\WithdrawalRequestResource;
use App\Models\LedgerEntry;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WalletController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    /** Query: bucket (all|available|escrow, default all). Balances, 6-month earnings series, and ledger. */
    public function index(Request $request): JsonResponse
    {
        $wallet = $this->wallets->walletFor($request->user());
        $profile = $request->user()->providerProfile;
        $minWithdrawal = (float) config('payments.min_withdrawal');

        $bucket = (string) $request->query('bucket', 'all');
        if (! in_array($bucket, ['all', LedgerEntry::BUCKET_AVAILABLE, LedgerEntry::BUCKET_ESCROW], true)) {
            $bucket = 'all';
        }

        $credits = $wallet->entries()
            ->where('bucket', LedgerEntry::BUCKET_AVAILABLE)
            ->where('amount', '>', 0)
            ->get(['amount', 'created_at']);

        $totalEarned = (float) $credits->sum('amount');

        $byMonth = $credits
            ->groupBy(fn (LedgerEntry $e) => $e->created_at->format('Y-m'))
            ->map(fn (Collection $g) => (float) $g->sum('amount'));

        $earnedThisMonth = (float) ($byMonth[Carbon::now()->format('Y-m')] ?? 0);

        $series = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonthsNoOverflow($i);
            $series->push(['label' => $month->format('M'), 'value' => (float) ($byMonth[$month->format('Y-m')] ?? 0)]);
        }

        $counts = [
            'all' => $wallet->entries()->count(),
            LedgerEntry::BUCKET_AVAILABLE => $wallet->entries()->where('bucket', LedgerEntry::BUCKET_AVAILABLE)->count(),
            LedgerEntry::BUCKET_ESCROW => $wallet->entries()->where('bucket', LedgerEntry::BUCKET_ESCROW)->count(),
        ];

        $query = $wallet->entries()->with('payment.booking');
        if ($bucket !== 'all') {
            $query->where('bucket', $bucket);
        }
        $entries = $query->latest('id')->paginate(20)->withQueryString();

        $withdrawalRequests = $profile ? $profile->withdrawalRequests()->latest()->limit(10)->get() : collect();

        return response()->json([
            'wallet' => new WalletResource($wallet),
            'total_earned' => $totalEarned,
            'earned_this_month' => $earnedThisMonth,
            'earnings_series' => $series,
            'ledger' => [
                'bucket' => $bucket,
                'counts' => $counts,
                'entries' => LedgerEntryResource::collection($entries),
                'pagination' => [
                    'current_page' => $entries->currentPage(),
                    'last_page' => $entries->lastPage(),
                    'total' => $entries->total(),
                ],
            ],
            'min_withdrawal' => $minWithdrawal,
            'withdrawal_requests' => WithdrawalRequestResource::collection($withdrawalRequests),
        ]);
    }
}
