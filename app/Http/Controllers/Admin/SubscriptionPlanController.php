<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::with('service')
            ->withCount('subscriptions')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $counts = [
            'total' => SubscriptionPlan::count(),
            'active' => SubscriptionPlan::where('is_active', true)->count(),
            'hidden' => SubscriptionPlan::where('is_active', false)->count(),
        ];

        return view('admin.subscription-plans.index', compact('plans', 'counts'));
    }

    public function create(): View
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('admin.subscription-plans.create', compact('services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['slug'] = SubscriptionPlan::generateSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        SubscriptionPlan::create($data);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Plan created.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan): View
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('admin.subscription-plans.edit', ['plan' => $subscriptionPlan, 'services' => $services]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $data = $this->validateData($request);

        $data['slug'] = SubscriptionPlan::generateSlug($data['name'], $subscriptionPlan->id);
        $data['is_active'] = $request->boolean('is_active');

        $subscriptionPlan->update($data);

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Plan updated.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        if ($subscriptionPlan->subscriptions()->exists()) {
            return redirect()
                ->route('admin.subscription-plans.index')
                ->with('error', 'Cannot delete a plan with active subscribers. Deactivate it instead.');
        }

        $subscriptionPlan->delete();

        return redirect()
            ->route('admin.subscription-plans.index')
            ->with('success', 'Plan deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'frequency_months' => ['required', 'integer', 'min:1', 'max:24'],
            'total_visits' => ['nullable', 'integer', 'min:1', 'max:100'],
            'price_per_visit' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
