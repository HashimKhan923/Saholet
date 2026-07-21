<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\ProviderProfileResource;
use App\Models\Bid;
use App\Models\Booking;
use App\Models\EmergencyRequest;
use App\Models\JobPost;
use App\Models\LedgerEntry;
use App\Models\ProviderProfile;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    /** Home-screen summary: counters, wallet, earnings trend, today's schedule, activity feed. */
    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->providerProfile;

        $data = [
            'profile' => $profile ? new ProviderProfileResource($profile) : null,
            'pending_bookings' => 0,
            'available_jobs' => 0,
            'open_emergencies' => 0,
            'wallet_available' => 0.0,
            'wallet_escrow' => 0.0,
            'earnings_month' => 0.0,
            'earnings_total' => 0.0,
            'earnings_delta' => null,
            'earnings_series' => [],
            'jobs_completed' => 0,
            'active_bookings' => 0,
            'completion_rate' => null,
            'response_minutes' => null,
            'bid_win_rate' => null,
            'bids_pending' => 0,
            'today_schedule' => [],
        ];

        if (! $profile || ! $profile->isApproved()) {
            return response()->json($data);
        }

        $wallet = $this->wallets->walletFor($request->user());
        $serviceIds = $profile->providerServices()->where('is_active', true)->pluck('service_id');

        $counts = Booking::where('provider_profile_id', $profile->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $n = fn (string $status): int => (int) ($counts[$status] ?? 0);

        $data['pending_bookings'] = $n(Booking::STATUS_PENDING);
        $data['jobs_completed'] = $n(Booking::STATUS_COMPLETED);
        $data['active_bookings'] = $n(Booking::STATUS_PENDING) + $n(Booking::STATUS_CONFIRMED) + $n(Booking::STATUS_IN_PROGRESS);

        $decided = $n(Booking::STATUS_COMPLETED) + $n(Booking::STATUS_CANCELLED);
        $data['completion_rate'] = $decided > 0 ? (int) round($n(Booking::STATUS_COMPLETED) / $decided * 100) : null;

        $data['available_jobs'] = JobPost::where('status', JobPost::STATUS_OPEN)->whereIn('service_id', $serviceIds)->count();

        $data['open_emergencies'] = EmergencyRequest::where('status', EmergencyRequest::STATUS_OPEN)
            ->whereIn('service_id', $serviceIds)
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($profile->city ?? ''))])
            ->count();

        $data['wallet_available'] = (float) $wallet->available_balance;
        $data['wallet_escrow'] = (float) $wallet->escrow_balance;

        $credits = LedgerEntry::where('wallet_id', $wallet->id)
            ->where('bucket', LedgerEntry::BUCKET_AVAILABLE)
            ->where('amount', '>', 0)
            ->get(['amount', 'created_at']);

        $data['earnings_total'] = (float) $credits->sum('amount');

        $byMonth = $credits
            ->groupBy(fn (LedgerEntry $e) => $e->created_at->format('Y-m'))
            ->map(fn (Collection $g) => (float) $g->sum('amount'));

        $thisMonth = (float) ($byMonth[Carbon::now()->format('Y-m')] ?? 0);
        $lastMonth = (float) ($byMonth[Carbon::now()->subMonthNoOverflow()->format('Y-m')] ?? 0);

        $data['earnings_month'] = $thisMonth;
        $data['earnings_delta'] = $lastMonth > 0
            ? (int) round(($thisMonth - $lastMonth) / $lastMonth * 100)
            : ($thisMonth > 0 ? 100 : null);

        $series = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonthsNoOverflow($i);
            $series[] = ['label' => $month->format('M'), 'value' => (float) ($byMonth[$month->format('Y-m')] ?? 0)];
        }
        $data['earnings_series'] = $series;

        $responded = Booking::where('provider_profile_id', $profile->id)
            ->whereNotNull('confirmed_at')
            ->latest('confirmed_at')
            ->take(30)
            ->get(['created_at', 'confirmed_at']);

        if ($responded->isNotEmpty()) {
            $data['response_minutes'] = (int) round(
                $responded->avg(fn (Booking $b) => $b->created_at->diffInMinutes($b->confirmed_at))
            );
        }

        $bidCounts = Bid::where('provider_profile_id', $profile->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $accepted = (int) ($bidCounts[Bid::STATUS_ACCEPTED] ?? 0);
        $rejected = (int) ($bidCounts[Bid::STATUS_REJECTED] ?? 0);

        $data['bids_pending'] = (int) ($bidCounts[Bid::STATUS_PENDING] ?? 0);
        $data['bid_win_rate'] = ($accepted + $rejected) > 0 ? (int) round($accepted / ($accepted + $rejected) * 100) : null;

        $todaySchedule = Booking::with(['consumer', 'service'])
            ->where('provider_profile_id', $profile->id)
            ->whereDate('scheduled_date', Carbon::today())
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->orderBy('scheduled_time')
            ->get();

        $data['today_schedule'] = BookingResource::collection($todaySchedule);

        return response()->json($data);
    }
}
