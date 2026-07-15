<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Booking;
use App\Models\JobPost;
use App\Models\Service;
use App\Models\ServiceArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JobController extends Controller
{
    public function __construct(private \App\Services\GeofenceService $geofence) {}

    public function index(Request $request): View
    {
        $jobs = JobPost::with('service')
            ->withCount(['bids as pending_bids_count' => fn ($q) => $q->where('status', Bid::STATUS_PENDING)])
            ->where('consumer_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('consumer.jobs.index', compact('jobs'));
    }

    public function create(Request $request): View
    {
        $services = Service::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedService = $request->query('service');
        $cities = ServiceArea::active()->pluck('city')->unique()->sort()->values();

        return view('consumer.jobs.create', compact('services', 'selectedService', 'cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'description' => ['required', 'string', 'max:2000'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:9999999'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'city' => ['required', 'string', 'max:120'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        if (! $this->geofence->isAllowed($data['city'])) {
            return back()->withInput()->with('error', 'Sorry, we’re not serving that city yet.');
        }

        $job = JobPost::create([
            'reference' => $this->generateJobReference(),
            'consumer_id' => $request->user()->id,
            'service_id' => $data['service_id'],
            'description' => $data['description'],
            'budget' => $data['budget'] ?? null,
            'preferred_date' => $data['preferred_date'] ?? null,
            'address' => $data['address'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'city' => $data['city'],
            'status' => JobPost::STATUS_OPEN,
        ]);

        foreach ($request->file('photos', []) as $photo) {
            $path = $photo->store("job-photos/{$job->id}", 'public');

            $job->photos()->create([
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getClientMimeType(),
                'size' => $photo->getSize(),
            ]);
        }

        return redirect()
            ->route('consumer.jobs.show', $job)
            ->with('success', 'Job posted. Providers can now bid.');
    }

    public function show(Request $request, JobPost $jobPost): View
    {
        $this->authorize('view', $jobPost);

        $jobPost->load([
            'service.category',
            'photos',
            'bids' => fn ($q) => $q->latest(),
            'bids.providerProfile.user',
            'bids.booking',
        ]);

        return view('consumer.jobs.show', compact('jobPost'));
    }

    public function cancel(Request $request, JobPost $jobPost): RedirectResponse
    {
        $this->authorize('cancel', $jobPost);

        if (! $jobPost->isOpen()) {
            return back()->with('error', 'Only open jobs can be cancelled.');
        }

        DB::transaction(function () use ($jobPost) {
            $jobPost->bids()->where('status', Bid::STATUS_PENDING)->update(['status' => Bid::STATUS_REJECTED]);
            $jobPost->update(['status' => JobPost::STATUS_CANCELLED]);
        });

        return back()->with('success', 'Job cancelled.');
    }

    public function acceptBid(Request $request, JobPost $jobPost, Bid $bid): RedirectResponse
    {
        $this->authorize('acceptBid', $jobPost);
        abort_unless($bid->job_post_id === $jobPost->id, 404);

        if (! $jobPost->isOpen()) {
            return back()->with('error', 'This job is no longer open.');
        }
        if (! $bid->isPending()) {
            return back()->with('error', 'This bid can no longer be accepted.');
        }

        $time = substr($bid->proposed_time, 0, 5);
        $scheduledAt = Carbon::parse($bid->proposed_date->toDateString() . ' ' . $time);

        if ($scheduledAt->isPast()) {
            return back()->with('error', 'This bid’s proposed time has passed. Please ask the provider for an updated bid or choose another.');
        }

        // Reuse Phase 4's slot-occupancy rule: no two active bookings at the same provider/date/time.
        $clash = Booking::where('provider_profile_id', $bid->provider_profile_id)
            ->where('scheduled_date', $bid->proposed_date->toDateString())
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->get(['scheduled_time'])
            ->contains(fn ($b) => substr($b->scheduled_time, 0, 5) === $time);

        if ($clash) {
            return back()->with('error', 'The provider already has a booking at that time. Please choose another bid.');
        }

        $jobPost->loadMissing('service');

        [$booking, $rejectedProviderUsers] = DB::transaction(function () use ($jobPost, $bid, $time) {
            $booking = Booking::create([
                'reference' => $this->generateBookingReference(),
                'consumer_id' => $jobPost->consumer_id,
                'provider_profile_id' => $bid->provider_profile_id,
                'service_id' => $jobPost->service_id,
                'scheduled_date' => $bid->proposed_date->toDateString(),
                'scheduled_time' => $time,
                'price' => $bid->amount,
                'duration_minutes' => $jobPost->service->duration_minutes,
                'address' => $jobPost->address,
                'notes' => $jobPost->description,
                'status' => Booking::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);

            $bid->update([
                'status' => Bid::STATUS_ACCEPTED,
                'booking_id' => $booking->id,
            ]);

            $rejectedProviderUsers = Bid::with('providerProfile.user')
                ->where('job_post_id', $jobPost->id)
                ->where('id', '!=', $bid->id)
                ->where('status', Bid::STATUS_PENDING)
                ->get()
                ->map(fn (Bid $b) => $b->providerProfile->user);

            $jobPost->bids()
                ->where('id', '!=', $bid->id)
                ->where('status', Bid::STATUS_PENDING)
                ->update(['status' => Bid::STATUS_REJECTED]);

            $jobPost->update([
                'status' => JobPost::STATUS_AWARDED,
                'awarded_at' => now(),
            ]);

            return [$booking, $rejectedProviderUsers];
        });

        $notifier = app(\App\Services\Notifier::class);

        $notifier->notify(
            $bid->providerProfile->user,
            'bid',
            'Your bid was accepted',
            'Your bid on ' . $jobPost->reference . ' was accepted. A confirmed booking (' . $booking->reference . ') has been created.',
            route('provider.bookings.show', $booking)
        );

        foreach ($rejectedProviderUsers as $providerUser) {
            $notifier->notify(
                $providerUser,
                'bid',
                'Job filled by another provider',
                'The job ' . $jobPost->reference . ' was awarded to another provider.',
                route('provider.jobs.index')
            );
        }

        return redirect()
            ->route('consumer.bookings.show', $booking)
            ->with('success', 'Bid accepted. A confirmed booking has been created.');
    }

    private function generateJobReference(): string
    {
        do {
            $ref = 'JOB-' . strtoupper(Str::random(6));
        } while (JobPost::where('reference', $ref)->exists());

        return $ref;
    }

    private function generateBookingReference(): string
    {
        do {
            $ref = 'BK-' . strtoupper(Str::random(6));
        } while (Booking::where('reference', $ref)->exists());

        return $ref;
    }
}