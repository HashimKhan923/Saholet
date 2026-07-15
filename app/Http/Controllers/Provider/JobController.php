<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\JobPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        if (! $profile || ! $profile->isApproved()) {
            return view('provider.jobs.index', [
                'approved'     => false,
                'jobs'         => collect(),
                'myBids'       => collect(),
                'myServiceIds' => collect(),
                'myBidsCount'  => 0,
            ]);
        }

        $serviceIds = $profile->providerServices()
            ->where('is_active', true)
            ->pluck('service_id');

        $jobs = JobPost::with(['service.category', 'consumer'])
            ->where('status', JobPost::STATUS_OPEN)
            ->whereIn('service_id', $serviceIds)
            ->withCount(['bids', 'photos'])
            ->latest()
            ->get();

        $myBids = Bid::where('provider_profile_id', $profile->id)
            ->whereIn('job_post_id', $jobs->pluck('id'))
            ->get()
            ->keyBy('job_post_id');

        return view('provider.jobs.index', [
            'approved'     => true,
            'jobs'         => $jobs,
            'myBids'       => $myBids,
            'myServiceIds' => $serviceIds->values(),
            'myBidsCount'  => $myBids->count(),
        ]);
    }

    public function show(Request $request, JobPost $jobPost): View
    {
        Gate::authorize('actAsApprovedProvider');
        $profile = $request->user()->providerProfile;

        $myBid = Bid::where('provider_profile_id', $profile->id)
            ->where('job_post_id', $jobPost->id)
            ->first();

        // Providers can view an open job, or any job they've already bid on.
        abort_unless($jobPost->isOpen() || $myBid, 404);

        $offersService = $profile->providerServices()
            ->where('service_id', $jobPost->service_id)
            ->where('is_active', true)
            ->exists();

        $jobPost->load(['service.category', 'consumer', 'photos']);

        $slots = $this->slotOptions();

        return view('provider.jobs.show', compact('jobPost', 'offersService', 'myBid', 'slots'));
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
}