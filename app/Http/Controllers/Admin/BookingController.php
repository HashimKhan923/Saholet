<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    /** Status tabs the index supports, in display order. */
    private const FILTERS = ['all', 'pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];

    public function index(Request $request): View
    {
        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, self::FILTERS, true)) {
            $filter = 'all';
        }

        $search = trim((string) $request->query('q', ''));

        $tally = Booking::selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = ['all' => (int) $tally->sum()];
        foreach (self::FILTERS as $status) {
            if ($status !== 'all') {
                $counts[$status] = (int) ($tally[$status] ?? 0);
            }
        }

        $query = Booking::with(['service', 'consumer', 'providerProfile.user', 'review']);

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('consumer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('providerProfile', fn ($p) => $p->where('business_name', 'like', "%{$search}%"))
                    ->orWhereHas('providerProfile.user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('service', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        $bookings = $query
            ->orderByRaw("CASE WHEN status IN ('pending','confirmed','in_progress') THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'counts', 'filter', 'search'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['service.category', 'consumer', 'providerProfile.user', 'payments', 'review', 'dispute', 'completionPhotos']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function create(): View
    {
        $consumers = User::where('role', User::ROLE_CONSUMER)->orderBy('name')->get(['id', 'name', 'email', 'phone']);

        $providers = ProviderProfile::approved()
            ->with(['user:id,name', 'providerServices' => fn ($q) => $q->where('is_active', true)->with('service:id,name')])
            ->orderBy('business_name')
            ->get();

        // provider_profile_id => [{ service_id, name, price }] — drives the client-side service filter.
        $providerServiceMap = $providers->mapWithKeys(fn ($p) => [
            $p->id => $p->providerServices->map(fn ($ps) => [
                'service_id' => $ps->service_id,
                'name' => $ps->service?->name,
                'price' => (float) $ps->price,
            ])->values(),
        ]);

        return view('admin.bookings.create', compact('consumers', 'providers', 'providerServiceMap'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'consumer_id' => ['required', 'exists:users,id'],
            'provider_profile_id' => ['required', 'exists:provider_profiles,id'],
            'service_id' => ['required', 'exists:services,id'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'scheduled_date' => ['required', 'date'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'address' => ['required', 'string', 'max:500'],
            'status' => ['required', 'in:pending,confirmed'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $provider = ProviderProfile::approved()->findOrFail($data['provider_profile_id']);

        $offering = $provider->providerServices()->where('service_id', $data['service_id'])->where('is_active', true)->first();
        if (! $offering) {
            return back()->withInput()->with('error', 'This provider does not currently offer the selected service.');
        }

        $clash = Booking::where('provider_profile_id', $provider->id)
            ->where('scheduled_date', $data['scheduled_date'])
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->get(['scheduled_time'])
            ->contains(fn ($b) => substr($b->scheduled_time, 0, 5) === $data['scheduled_time']);

        if ($clash) {
            return back()->withInput()->with('error', 'This provider already has a booking at that date and time.');
        }

        $service = $offering->service ?? \App\Models\Service::findOrFail($data['service_id']);

        $booking = Booking::create([
            'reference' => $this->generateReference(),
            'consumer_id' => $data['consumer_id'],
            'provider_profile_id' => $provider->id,
            'service_id' => $data['service_id'],
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $data['scheduled_time'],
            'price' => $data['price'],
            'duration_minutes' => $service->duration_minutes,
            'address' => $data['address'],
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'],
            'source' => 'manual',
            'confirmed_at' => $data['status'] === 'confirmed' ? now() : null,
        ]);

        $notifier = app(Notifier::class);
        $notifier->notify($provider->user, 'booking', 'New booking assigned', 'A booking (' . $booking->reference . ') was created for you by our team.', route('provider.bookings.show', $booking));
        $notifier->notify($booking->consumer, 'booking', 'Booking created', 'Your booking (' . $booking->reference . ') has been set up by our team.', route('consumer.bookings.show', $booking));

        return redirect()->route('admin.bookings.show', $booking)->with('success', 'Booking created.');
    }

    private function generateReference(): string
    {
        do {
            $ref = 'BK-' . strtoupper(Str::random(6));
        } while (Booking::where('reference', $ref)->exists());

        return $ref;
    }
}
