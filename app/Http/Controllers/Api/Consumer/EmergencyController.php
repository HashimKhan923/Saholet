<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Events\EmergencyRequestCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmergencyRequestResource;
use App\Models\EmergencyRequest;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Services\GeofenceService;
use App\Services\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmergencyController extends Controller
{
    public function __construct(private GeofenceService $geofence) {}

    public function index(Request $request): JsonResponse
    {
        $emergencies = EmergencyRequest::with(['service.category', 'matchedProvider.user', 'booking'])
            ->where('consumer_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'emergencies' => EmergencyRequestResource::collection($emergencies),
            'pagination' => [
                'current_page' => $emergencies->currentPage(),
                'last_page' => $emergencies->lastPage(),
                'total' => $emergencies->total(),
            ],
        ]);
    }

    /** Body: service_id, address, city, notes? */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'exists:services,id'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service = Service::where('id', $data['service_id'])->where('is_active', true)->first();
        if (! $service) {
            return response()->json(['message' => 'That service is currently unavailable.'], 422);
        }

        if (! $this->geofence->isAllowed($data['city'])) {
            return response()->json(['message' => 'Sorry, we\'re not serving that city yet.'], 422);
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

        $emergency->load('service.category');

        return response()->json(['emergency' => new EmergencyRequestResource($emergency)], 201);
    }

    public function show(Request $request, EmergencyRequest $emergencyRequest): JsonResponse
    {
        $this->authorize('view', $emergencyRequest);

        $emergencyRequest->load(['service.category', 'booking', 'matchedProvider.user']);

        return response()->json(['emergency' => new EmergencyRequestResource($emergencyRequest)]);
    }

    public function cancel(Request $request, EmergencyRequest $emergencyRequest): JsonResponse
    {
        $this->authorize('cancel', $emergencyRequest);

        if (! $emergencyRequest->isOpen()) {
            return response()->json(['message' => 'Only an open request can be cancelled.'], 422);
        }

        $emergencyRequest->update([
            'status' => EmergencyRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return response()->json(['message' => 'Emergency request cancelled.']);
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
