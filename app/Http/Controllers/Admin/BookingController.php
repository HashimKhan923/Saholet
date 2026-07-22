<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
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

        $query = Booking::with(['service', 'consumer', 'providerProfile.user']);

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
        $booking->load(['service.category', 'consumer', 'providerProfile.user', 'payments', 'review', 'dispute']);

        return view('admin.bookings.show', compact('booking'));
    }
}
