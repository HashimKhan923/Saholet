<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use App\Models\Subscription;
use App\Services\Notifier;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending_assignment');

        $validStatuses = ['pending_assignment', 'active', 'completed', 'cancelled', 'all'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'pending_assignment';
        }

        $query = Subscription::with(['plan.service', 'consumer:id,name'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $subscriptions = $query->paginate(15)->withQueryString();

        return view('admin.subscriptions.index', compact('subscriptions', 'status'));
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load(['plan.service', 'consumer:id,name,email,phone', 'providerProfile.user', 'bookings' => fn ($q) => $q->latest('scheduled_date')]);

        $eligibleProviders = collect();
        if ($subscription->isPendingAssignment()) {
            $eligibleProviders = ProviderProfile::approved()
                ->whereHas('providerServices', fn ($q) => $q->where('service_id', $subscription->plan->service_id)->where('is_active', true))
                ->with('user:id,name')
                ->get();
        }

        return view('admin.subscriptions.show', compact('subscription', 'eligibleProviders'));
    }

    public function assignProvider(Request $request, Subscription $subscription, SubscriptionService $subscriptions): RedirectResponse
    {
        if (! $subscription->isPendingAssignment()) {
            return back()->with('error', 'This subscription already has a provider assigned.');
        }

        $data = $request->validate([
            'provider_profile_id' => ['required', 'exists:provider_profiles,id'],
            'scheduled_time' => ['required', 'date_format:H:i'],
        ]);

        $provider = ProviderProfile::approved()->findOrFail($data['provider_profile_id']);

        $booking = $subscriptions->assignProvider($subscription, $provider, $data['scheduled_time']);

        app(Notifier::class)->notify(
            $provider->user,
            'subscription',
            'New subscription assignment',
            'You’ve been assigned to a recurring plan: ' . $subscription->plan->name . '.',
            route('provider.bookings.show', $booking)
        );

        return redirect()
            ->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Provider assigned and first visit scheduled.');
    }
}
