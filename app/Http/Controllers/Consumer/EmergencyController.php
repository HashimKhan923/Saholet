<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\EmergencyRequest;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Services\GeofenceService;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Events\EmergencyRequestCreated;

class EmergencyController extends Controller
{
    public function __construct(private GeofenceService $geofence) {}

public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        if (! $profile || ! $profile->isApproved()) {
            return view('provider.emergencies.index', [
                'approved'     => false,
                'requests'     => collect(),
                'myServiceIds' => collect(),
                'myPrices'     => collect(),
                'myCity'       => '',
                'profileId'    => null,
            ]);
        }

        $offerings = $profile->providerServices()
            ->where('is_active', true)
            ->get(['service_id', 'price']);

        $serviceIds = $offerings->pluck('service_id');

        $requests = EmergencyRequest::with(['service.category', 'consumer'])
            ->where('status', EmergencyRequest::STATUS_OPEN)
            ->whereIn('service_id', $serviceIds)
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($profile->city ?? ''))])
            ->latest()
            ->get();

        return view('provider.emergencies.index', [
            'approved'     => true,
            'requests'     => $requests,
            'myServiceIds' => $serviceIds->values(),
            // service_id => the provider's own price, so a live-inserted card can show a payout.
            'myPrices'     => $offerings->mapWithKeys(fn ($o) => [$o->service_id => (int) $o->price]),
            'myCity'       => mb_strtolower(trim($profile->city ?? '')),
            'profileId'    => $profile->id,
        ]);
    }

    public function create(Request $request): View
    {
        $services = Service::with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $cities = ServiceArea::active()->pluck('city')->unique()->sort()->values();

        return view('consumer.emergencies.create', compact('services', 'cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service = Service::where('id', $data['service_id'])->where('is_active', true)->first();
        if (! $service) {
            return back()->withInput()->with('error', 'That service is currently unavailable.');
        }

        if (! $this->geofence->isAllowed($data['city'])) {
            return back()->withInput()->with('error', 'Sorry, we’re not serving that city yet.');
        }

        $emergency = EmergencyRequest::create([
            'reference' => $this->generateReference(),
            'consumer_id' => $request->user()->id,
            'service_id' => $service->id,
            'address' => $data['address'],
            'city' => $data['city'],
            'notes' => $data['notes'] ?? null,
            'status' => EmergencyRequest::STATUS_OPEN,
        ]);

        $this->notifyMatchingProviders($emergency, $service);

        return redirect()
            ->route('consumer.emergencies.show', $emergency)
            ->with('success', 'Emergency request sent. We’re alerting available providers near you.');
    }

    public function show(Request $request, EmergencyRequest $emergencyRequest): View
    {
        $this->authorize('view', $emergencyRequest);

        $emergencyRequest->load(['service.category', 'booking', 'matchedProvider.user']);

        return view('consumer.emergencies.show', compact('emergencyRequest'));
    }

    public function cancel(Request $request, EmergencyRequest $emergencyRequest): RedirectResponse
    {
        $this->authorize('cancel', $emergencyRequest);

        if (! $emergencyRequest->isOpen()) {
            return back()->with('error', 'Only an open request can be cancelled.');
        }

        $emergencyRequest->update([
            'status' => EmergencyRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Emergency request cancelled.');
    }

    private function notifyMatchingProviders(EmergencyRequest $emergency, Service $service): void
    {
        $cap = (int) config('emergency.max_providers_notified', 15);

        $profiles = ProviderProfile::query()
            ->where('status', ProviderProfile::STATUS_APPROVED)
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim($emergency->city))])
            ->whereHas('providerServices', fn ($q) => $q->where('service_id', $service->id)->where('is_active', true))
            ->with('user')
            ->orderByDesc('rating_avg')
            ->orderByDesc('reviews_count')
            ->orderByDesc('experience_years')
            ->limit($cap)
            ->get();

        if ($profiles->isEmpty()) {
            return;
        }

        $notifier = app(Notifier::class);

        foreach ($profiles as $profile) {
            $notifier->notify(
                $profile->user,
                'booking',
                'Emergency request nearby',
                'An urgent ' . $service->name . ' request (' . $emergency->reference . ') is available in ' . $emergency->city . '.',
                route('provider.emergencies.index')
            );
        }

        // Push the card onto every matched provider's board instantly.
        // Best-effort: a broadcast failure must never block the request itself.
        try {
            broadcast(new EmergencyRequestCreated($emergency, $profiles->pluck('id')->all()));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function generateReference(): string
    {
        do {
            $ref = 'EMG-' . strtoupper(Str::random(6));
        } while (EmergencyRequest::where('reference', $ref)->exists());

        return $ref;
    }
}