<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\EmergencyRequest;
use App\Models\Service;
use App\Services\GeofenceService;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmergencyController extends Controller
{
    public function __construct(private GeofenceService $geofence) {}

    public function index(Request $request): View
    {
        $requests = EmergencyRequest::with(['service.category'])
            ->where('consumer_id', $request->user()->id)
            ->latest()
            ->get();

        return view('consumer.emergencies.index', compact('requests'));
    }

    public function create(Request $request): View
    {
        $services = Service::with('category')
            ->where('is_active', true)
            ->where('is_emergency_available', true)
            ->orderBy('name')
            ->get();

        return view('consumer.emergencies.create', compact('services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'city' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service = Service::where('id', $data['service_id'])
            ->where('is_active', true)
            ->where('is_emergency_available', true)
            ->first();

        if (! $service) {
            return back()->withInput()->with('error', 'That service is currently unavailable.');
        }

        if (! $this->geofence->isAllowed($data['latitude'] ?? null, $data['longitude'] ?? null)) {
            return back()->withInput()->with('error', 'Sorry, we’re not serving that city yet.');
        }

        $emergency = EmergencyRequest::create([
            'reference' => $this->generateReference(),
            'consumer_id' => $request->user()->id,
            'service_id' => $service->id,
            'address' => $data['address'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'city' => $data['city'],
            'notes' => $data['notes'] ?? null,
            'status' => EmergencyRequest::STATUS_OPEN,
        ]);

        app(Notifier::class)->notifyAdmins(
            'emergency',
            'New emergency request',
            $service->name . ' request (' . $emergency->reference . ') in ' . $emergency->city . '.',
            route('admin.emergencies.show', $emergency)
        );

        return redirect()
            ->route('consumer.emergencies.show', $emergency)
            ->with('success', 'Emergency request sent. Our team will review it and send you a price shortly.');
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

        if (! in_array($emergencyRequest->status, [EmergencyRequest::STATUS_OPEN, EmergencyRequest::STATUS_QUOTED], true)) {
            return back()->with('error', 'This request can no longer be cancelled.');
        }

        $emergencyRequest->update([
            'status' => EmergencyRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Emergency request cancelled.');
    }

    public function acceptQuote(Request $request, EmergencyRequest $emergencyRequest): RedirectResponse
    {
        $this->authorize('respond', $emergencyRequest);

        if (! $emergencyRequest->isQuoted()) {
            return back()->with('error', 'This request does not have a pending quote.');
        }

        $emergencyRequest->update([
            'status' => EmergencyRequest::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        app(Notifier::class)->notifyAdmins(
            'emergency',
            'Emergency quote accepted',
            'The customer accepted the Rs. ' . number_format((float) $emergencyRequest->quoted_price, 0) . ' quote for ' . $emergencyRequest->reference . ' — assign a provider.',
            route('admin.emergencies.show', $emergencyRequest)
        );

        return back()->with('success', 'Quote accepted. We’ll assign a provider shortly.');
    }

    public function declineQuote(Request $request, EmergencyRequest $emergencyRequest): RedirectResponse
    {
        $this->authorize('respond', $emergencyRequest);

        if (! $emergencyRequest->isQuoted()) {
            return back()->with('error', 'This request does not have a pending quote.');
        }

        $emergencyRequest->update([
            'status' => EmergencyRequest::STATUS_DECLINED,
            'declined_at' => now(),
        ]);

        app(Notifier::class)->notifyAdmins(
            'emergency',
            'Emergency quote declined',
            'The customer declined the quote for ' . $emergencyRequest->reference . '.',
            route('admin.emergencies.show', $emergencyRequest)
        );

        return back()->with('success', 'Quote declined.');
    }

    private function generateReference(): string
    {
        do {
            $ref = 'EMG-' . strtoupper(Str::random(6));
        } while (EmergencyRequest::where('reference', $ref)->exists());

        return $ref;
    }
}
