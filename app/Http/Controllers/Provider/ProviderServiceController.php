<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\ProviderService;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProviderServiceController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        if (! $profile || ! $profile->isApproved()) {
            return view('provider.services.index', [
                'approved'      => false,
                'offered'       => collect(),
                'available'     => collect(),
                'bookingCounts' => collect(),
            ]);
        }

        $offered = $profile->providerServices()
            ->with('service.category')
            ->get()
            ->sortBy(fn (ProviderService $ps) => $ps->service->name)
            ->values();

        $offeredIds = $offered->pluck('service_id')->all();

        $available = Service::with('category')
            ->where('is_active', true)
            ->whereNotIn('id', $offeredIds)
            ->orderBy('name')
            ->get();

        // How many bookings this provider has taken per service — a useful demand signal.
        $bookingCounts = Booking::where('provider_profile_id', $profile->id)
            ->selectRaw('service_id, COUNT(*) as aggregate')
            ->groupBy('service_id')
            ->pluck('aggregate', 'service_id');

        return view('provider.services.index', [
            'approved'      => true,
            'offered'       => $offered,
            'available'     => $available,
            'bookingCounts' => $bookingCounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $this->approvedProfile($request);

        $data = $request->validate([
            'service_id' => [
                'required',
                'exists:services,id',
                Rule::unique('provider_services')->where('provider_profile_id', $profile->id),
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
        ]);

        $profile->providerServices()->create([
            'service_id' => $data['service_id'],
            'price' => $data['price'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Service added to your offerings.');
    }

    public function update(Request $request, ProviderService $providerService): RedirectResponse
    {
        $this->approvedProfile($request);
        $this->authorize('update', $providerService);

        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $providerService->update([
            'price' => $data['price'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Service updated.');
    }

    public function destroy(Request $request, ProviderService $providerService): RedirectResponse
    {
        $this->approvedProfile($request);
        $this->authorize('delete', $providerService);

        $providerService->delete();

        return back()->with('success', 'Service removed from your offerings.');
    }

    private function approvedProfile(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }
}