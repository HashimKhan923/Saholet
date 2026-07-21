<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\ServiceArea;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\GeofenceService;
use App\Services\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function __construct(private GeofenceService $geofence) {}

    public function index(Request $request): JsonResponse
    {
        $subscriptions = Subscription::where('consumer_id', $request->user()->id)
            ->with(['plan.service', 'bookings' => fn ($q) => $q->latest('scheduled_date')->limit(1)])
            ->latest()
            ->paginate(15);

        return response()->json([
            'subscriptions' => SubscriptionResource::collection($subscriptions),
            'pagination' => [
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage(),
                'total' => $subscriptions->total(),
            ],
        ]);
    }

    /** Body: address, latitude?, longitude?, city, start_date. */
    public function store(Request $request, SubscriptionPlan $plan): JsonResponse
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
            return response()->json(['message' => 'Sorry, we\'re not serving that city yet.'], 422);
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

        $subscription->load('plan.service');

        return response()->json(['subscription' => new SubscriptionResource($subscription)], 201);
    }

    public function show(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('view', $subscription);

        $subscription->load(['plan.service', 'providerProfile.user', 'bookings' => fn ($q) => $q->latest('scheduled_date')]);

        return response()->json(['subscription' => new SubscriptionResource($subscription)]);
    }

    public function cancel(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('cancel', $subscription);

        if (! $subscription->isCancellable()) {
            return response()->json(['message' => 'This subscription can no longer be cancelled.'], 422);
        }

        $subscription->update([
            'status' => Subscription::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('reason'),
        ]);

        return response()->json(['message' => 'Subscription cancelled. Any already-scheduled visits are unaffected.']);
    }

    private function generateReference(): string
    {
        do {
            $ref = 'SUB-' . strtoupper(Str::random(8));
        } while (Subscription::where('reference', $ref)->exists());

        return $ref;
    }
}
