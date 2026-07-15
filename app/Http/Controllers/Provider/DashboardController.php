<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Booking;
use App\Models\EmergencyRequest;
use App\Models\JobPost;
use App\Models\LedgerEntry;
use App\Models\ProviderProfile;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        $data = [
            'profile'          => $profile,
            'pendingBookings'  => 0,
            'availableJobs'    => 0,
            'openEmergencies'  => 0,
            'walletAvailable'  => 0.0,
            'walletEscrow'     => 0.0,
            'earningsMonth'    => 0.0,
            'earningsTotal'    => 0.0,
            'earningsDelta'    => null,
            'earningsSeries'   => collect(),
            'jobsCompleted'    => 0,
            'activeBookings'   => 0,
            'completionRate'   => null,
            'responseMinutes'  => null,
            'bidWinRate'       => null,
            'bidsPending'      => 0,
            'todaySchedule'    => collect(),
            'activity'         => collect(),
        ];

        if (! $profile || ! $profile->isApproved()) {
            return view('provider.dashboard', $data);
        }

        $wallet = $this->wallets->walletFor($request->user());

        $serviceIds = $profile->providerServices()
            ->where('is_active', true)
            ->pluck('service_id');

        /* ── Booking counters ─────────────────────────────────────────── */
        $counts = Booking::where('provider_profile_id', $profile->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $n = fn (string $status): int => (int) ($counts[$status] ?? 0);

        $data['pendingBookings'] = $n(Booking::STATUS_PENDING);
        $data['jobsCompleted']   = $n(Booking::STATUS_COMPLETED);
        $data['activeBookings']  = $n(Booking::STATUS_PENDING)
            + $n(Booking::STATUS_CONFIRMED)
            + $n(Booking::STATUS_IN_PROGRESS);

        $decided = $n(Booking::STATUS_COMPLETED) + $n(Booking::STATUS_CANCELLED);
        $data['completionRate'] = $decided > 0
            ? (int) round($n(Booking::STATUS_COMPLETED) / $decided * 100)
            : null;

        /* ── Opportunity counters ─────────────────────────────────────── */
        $data['availableJobs'] = JobPost::where('status', JobPost::STATUS_OPEN)
            ->whereIn('service_id', $serviceIds)
            ->count();

        $data['openEmergencies'] = EmergencyRequest::where('status', EmergencyRequest::STATUS_OPEN)
            ->whereIn('service_id', $serviceIds)
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($profile->city ?? ''))])
            ->count();

        /* ── Wallet + earnings (from the append-only ledger) ──────────── */
        $data['walletAvailable'] = (float) $wallet->available_balance;
        $data['walletEscrow']    = (float) $wallet->escrow_balance;

        $credits = LedgerEntry::where('wallet_id', $wallet->id)
            ->where('bucket', LedgerEntry::BUCKET_AVAILABLE)
            ->where('amount', '>', 0)
            ->get(['amount', 'created_at']);

        $data['earningsTotal'] = (float) $credits->sum('amount');

        $byMonth = $credits
            ->groupBy(fn (LedgerEntry $e) => $e->created_at->format('Y-m'))
            ->map(fn (Collection $g) => (float) $g->sum('amount'));

        $thisMonth = (float) ($byMonth[Carbon::now()->format('Y-m')] ?? 0);
        $lastMonth = (float) ($byMonth[Carbon::now()->subMonthNoOverflow()->format('Y-m')] ?? 0);

        $data['earningsMonth'] = $thisMonth;
        $data['earningsDelta'] = $lastMonth > 0
            ? (int) round(($thisMonth - $lastMonth) / $lastMonth * 100)
            : ($thisMonth > 0 ? 100 : null);

        $series = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonthsNoOverflow($i);
            $series->push([
                'label' => $month->format('M'),
                'value' => (float) ($byMonth[$month->format('Y-m')] ?? 0),
            ]);
        }
        $data['earningsSeries'] = $series;

        /* ── Average response time (last 30 confirmations) ────────────── */
        $responded = Booking::where('provider_profile_id', $profile->id)
            ->whereNotNull('confirmed_at')
            ->latest('confirmed_at')
            ->take(30)
            ->get(['created_at', 'confirmed_at']);

        if ($responded->isNotEmpty()) {
            $data['responseMinutes'] = (int) round(
                $responded->avg(fn (Booking $b) => $b->created_at->diffInMinutes($b->confirmed_at))
            );
        }

        /* ── Bid win rate ─────────────────────────────────────────────── */
        $bidCounts = Bid::where('provider_profile_id', $profile->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $accepted = (int) ($bidCounts[Bid::STATUS_ACCEPTED] ?? 0);
        $rejected = (int) ($bidCounts[Bid::STATUS_REJECTED] ?? 0);

        $data['bidsPending'] = (int) ($bidCounts[Bid::STATUS_PENDING] ?? 0);
        $data['bidWinRate']  = ($accepted + $rejected) > 0
            ? (int) round($accepted / ($accepted + $rejected) * 100)
            : null;

        /* ── Today's schedule ─────────────────────────────────────────── */
        $data['todaySchedule'] = Booking::with(['consumer', 'service'])
            ->where('provider_profile_id', $profile->id)
            ->whereDate('scheduled_date', Carbon::today())
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->orderBy('scheduled_time')
            ->get();

        /* ── Activity feed ────────────────────────────────────────────── */
        $data['activity'] = $this->activityFeed($profile, $wallet->id);

        return view('provider.dashboard', $data);
    }

    /** Merge recent bookings, bids and earnings into one reverse-chronological feed. */
    private function activityFeed(ProviderProfile $profile, int $walletId): Collection
    {
        $bookings = Booking::with('service')
            ->where('provider_profile_id', $profile->id)
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn (Booking $b) => [
                'icon'  => 'booking',
                'tone'  => match ($b->status) {
                    Booking::STATUS_COMPLETED => 'brand',
                    Booking::STATUS_CANCELLED => 'red',
                    Booking::STATUS_PENDING   => 'amber',
                    default                   => 'sky',
                },
                'title' => 'Booking ' . str_replace('_', ' ', $b->status),
                'meta'  => ($b->service?->name ?? 'Service') . ' · ' . $b->reference,
                'at'    => $b->updated_at,
                'url'   => route('provider.bookings.show', $b),
            ]);

        $bids = Bid::with('jobPost')
            ->where('provider_profile_id', $profile->id)
            ->latest('updated_at')
            ->take(6)
            ->get()
            ->map(fn (Bid $bid) => [
                'icon'  => 'bid',
                'tone'  => match ($bid->status) {
                    Bid::STATUS_ACCEPTED => 'brand',
                    Bid::STATUS_REJECTED => 'red',
                    default              => 'slate',
                },
                'title' => 'Bid ' . $bid->status,
                'meta'  => 'Rs. ' . number_format((float) $bid->amount, 0)
                    . ' · ' . ($bid->jobPost?->reference ?? 'Job post'),
                'at'    => $bid->updated_at,
                'url'   => route('provider.bids.index'),
            ]);

        $earnings = LedgerEntry::where('wallet_id', $walletId)
            ->where('bucket', LedgerEntry::BUCKET_AVAILABLE)
            ->where('amount', '>', 0)
            ->latest('created_at')
            ->take(6)
            ->get()
            ->map(fn (LedgerEntry $e) => [
                'icon'  => 'earning',
                'tone'  => 'brand',
                'title' => 'Earned Rs. ' . number_format((float) $e->amount, 0),
                'meta'  => $e->description,
                'at'    => $e->created_at,
                'url'   => route('provider.wallet.index'),
            ]);

        return $bookings
            ->concat($bids)
            ->concat($earnings)
            ->sortByDesc('at')
            ->take(8)
            ->values();
    }
}