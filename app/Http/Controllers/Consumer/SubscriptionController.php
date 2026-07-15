<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\ServiceArea;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\GeofenceService;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(private GeofenceService $geofence) {}

    public function index(Request $request): View
    {
        $subscriptions = Subscription::where('consumer_id', $request->user()->id)
            ->with(['plan.service', 'bookings' => fn ($q) => $q->latest('scheduled_date')->limit(1)])
            ->latest()
            ->paginate(15);

        return view('consumer.subscriptions.index', compact('subscriptions'));
    }

    public function create(SubscriptionPlan $plan): View
    {
        abort_unless($plan->is_active, 404);

        $cities = ServiceArea::active()->pluck('city')->unique()->sort()->values();

        return view('consumer.subscriptions.create', compact('plan', 'cities'));
    }

    public function store(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        $data = $request->validate([
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'city' => ['required', 'string', 'max:120'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        if (! $this->geofence->isAllowed($data['city'])) {
            return back()->withInput()->with('error', 'Sorry, we’re not serving that city yet.');
        }

        $subscription = DB::transaction(fn () => Subscription::create([
            'reference' => $this->generateReference(),
            'subscription_plan_id' => $plan->id,
            'consumer_id' => $request->user()->id,
            'corporate_account_id' => $request->user()->corporate_account_id,
            'address' => $data['address'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'city' => $data['city'],
            'status' => Subscription::STATUS_PENDING_ASSIGNMENT,
            'next_visit_date' => $data['start_date'],
        ]));

        app(Notifier::class)->notifyAdmins(
            'subscription',
            'New subscription request',
            $plan->name . ' — new subscriber awaiting provider assignment.',
            route('admin.subscriptions.show', $subscription)
        );

        return redirect()
            ->route('consumer.subscriptions.show', $subscription)
            ->with('success', 'Subscribed! We’ll assign a provider and confirm your first visit shortly.');
    }

    public function show(Request $request, Subscription $subscription): View
    {
        $this->authorize('view', $subscription);

        $subscription->load(['plan.service', 'providerProfile.user', 'bookings' => fn ($q) => $q->latest('scheduled_date')]);

        return view('consumer.subscriptions.show', compact('subscription'));
    }

    public function cancel(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorize('cancel', $subscription);

        if (! $subscription->isCancellable()) {
            return back()->with('error', 'This subscription can no longer be cancelled.');
        }

        $subscription->update([
            'status' => Subscription::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('reason'),
        ]);

        return redirect()
            ->route('consumer.subscriptions.index')
            ->with('success', 'Subscription cancelled. Any already-scheduled visits are unaffected.');
    }

    private function generateReference(): string
    {
        do {
            $ref = 'SUB-' . strtoupper(Str::random(8));
        } while (Subscription::where('reference', $ref)->exists());

        return $ref;
    }
}
