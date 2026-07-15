<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\JobPost;
use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BidController extends Controller
{
    /** Status tabs the bids index supports, in display order. */
    private const BID_FILTERS = ['all', 'pending', 'accepted', 'rejected', 'withdrawn'];

    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, self::BID_FILTERS, true)) {
            $filter = 'all';
        }

        if (! $profile || ! $profile->isApproved()) {
            return view('provider.bids.index', [
                'bids'     => collect(),
                'counts'   => array_fill_keys(self::BID_FILTERS, 0),
                'filter'   => $filter,
                'winRate'  => null,
                'pipeline' => 0.0,
            ]);
        }

        $tally = Bid::where('provider_profile_id', $profile->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = ['all' => (int) $tally->sum()];
        foreach (self::BID_FILTERS as $status) {
            if ($status !== 'all') {
                $counts[$status] = (int) ($tally[$status] ?? 0);
            }
        }

        $decided = $counts['accepted'] + $counts['rejected'];
        $winRate = $decided > 0 ? (int) round($counts['accepted'] / $decided * 100) : null;

        // Value of everything still in play.
        $pipeline = (float) Bid::where('provider_profile_id', $profile->id)
            ->where('status', Bid::STATUS_PENDING)
            ->sum('amount');

        $query = Bid::with(['jobPost.service', 'booking'])
            ->where('provider_profile_id', $profile->id);

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        $bids = $query
            ->orderByRaw("CASE WHEN status = 'accepted' THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END")
            ->latest()
            ->get();

        return view('provider.bids.index', compact('bids', 'counts', 'filter', 'winRate', 'pipeline'));
    }

    public function store(Request $request, JobPost $jobPost): RedirectResponse
    {
        $profile = $this->approvedProfile($request);

        if (! $jobPost->isOpen()) {
            return back()->with('error', 'This job is no longer open for bids.');
        }

        $this->assertOffersService($profile, $jobPost);

        if (Bid::where('job_post_id', $jobPost->id)->where('provider_profile_id', $profile->id)->exists()) {
            return back()->with('error', 'You have already placed a bid on this job.');
        }

        $data = $this->validateBid($request);

       $jobPost->bids()->create([
            'provider_profile_id' => $profile->id,
            'amount' => $data['amount'],
            'proposed_date' => $data['proposed_date'],
            'proposed_time' => $data['proposed_time'],
            'message' => $data['message'] ?? null,
            'status' => Bid::STATUS_PENDING,
        ]);

        app(\App\Services\Notifier::class)->notify(
            $jobPost->consumer,
            'bid',
            'New bid on your job',
            'You received a new bid on ' . $jobPost->reference . '.',
            route('consumer.jobs.show', $jobPost)
        );

        return back()->with('success', 'Bid submitted.');
    }

    public function update(Request $request, Bid $bid): RedirectResponse
    {
        $this->approvedProfile($request);
        $this->authorize('update', $bid);

        if (! $bid->isPending()) {
            return back()->with('error', 'Only pending bids can be edited.');
        }
        if (! $bid->jobPost->isOpen()) {
            return back()->with('error', 'This job is no longer open.');
        }

        $data = $this->validateBid($request);

        $bid->update([
            'amount' => $data['amount'],
            'proposed_date' => $data['proposed_date'],
            'proposed_time' => $data['proposed_time'],
            'message' => $data['message'] ?? null,
        ]);

        return back()->with('success', 'Bid updated.');
    }

    public function destroy(Request $request, Bid $bid): RedirectResponse
    {
        $this->approvedProfile($request);
        $this->authorize('delete', $bid);

        if (! $bid->isPending()) {
            return back()->with('error', 'Only pending bids can be withdrawn.');
        }

        $bid->update(['status' => Bid::STATUS_WITHDRAWN]);

        return back()->with('success', 'Bid withdrawn.');
    }

    private function validateBid(Request $request): array
    {
        $slotValues = array_column($this->slotOptions(), 'value');

        return $request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'proposed_date' => ['required', 'date', 'after_or_equal:today'],
            'proposed_time' => ['required', Rule::in($slotValues)],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /** @return array<int, array{value:string,label:string}> */
    private function slotOptions(): array
    {
        $start = (int) config('booking.slot_start_hour');
        $end = (int) config('booking.slot_end_hour');
        $interval = (int) config('booking.slot_interval_minutes');

        $slots = [];
        $cursor = now()->startOfDay()->setTime($start, 0);
        $endAt = now()->startOfDay()->setTime($end, 0);

        while ($cursor < $endAt) {
            $slots[] = ['value' => $cursor->format('H:i'), 'label' => $cursor->format('g:i A')];
            $cursor->addMinutes($interval);
        }

        return $slots;
    }

    private function approvedProfile(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }

    private function assertOffersService(ProviderProfile $profile, JobPost $jobPost): void
    {
        $offers = $profile->providerServices()
            ->where('service_id', $jobPost->service_id)
            ->where('is_active', true)
            ->exists();

        abort_unless($offers, 403, 'You do not offer this service.');
    }
}