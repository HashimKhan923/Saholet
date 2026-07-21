<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobPostResource;
use App\Models\Bid;
use App\Models\JobPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class JobController extends Controller
{
    /** Open jobs matching services this provider offers, plus their own bid on each (if any). */
    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->providerProfile;

        if (! $profile || ! $profile->isApproved()) {
            return response()->json(['jobs' => []]);
        }

        $serviceIds = $profile->providerServices()->where('is_active', true)->pluck('service_id');

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

        $jobs->each(function (JobPost $job) use ($myBids) {
            $job->setRelation('myBid', $myBids->get($job->id));
        });

        return response()->json(['jobs' => JobPostResource::collection($jobs)]);
    }

    public function show(Request $request, JobPost $jobPost): JsonResponse
    {
        Gate::authorize('actAsApprovedProvider');
        $profile = $request->user()->providerProfile;

        $myBid = Bid::where('provider_profile_id', $profile->id)
            ->where('job_post_id', $jobPost->id)
            ->first();

        abort_unless($jobPost->isOpen() || $myBid, 404);

        $offersService = $profile->providerServices()
            ->where('service_id', $jobPost->service_id)
            ->where('is_active', true)
            ->exists();

        $jobPost->load(['service.category', 'consumer', 'photos']);
        $jobPost->setRelation('myBid', $myBid);

        return response()->json([
            'job' => new JobPostResource($jobPost),
            'offers_service' => $offersService,
            'slot_options' => $this->slotOptions(),
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
}
