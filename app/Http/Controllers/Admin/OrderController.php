<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const FILTERS = ['all', 'pending', 'confirmed', 'ready', 'completed', 'cancelled'];

    public function index(Request $request): View
    {
        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, self::FILTERS, true)) {
            $filter = 'all';
        }

        $search = trim((string) $request->query('q', ''));

        $tally = Order::selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');

        $counts = ['all' => (int) $tally->sum()];
        foreach (self::FILTERS as $status) {
            if ($status !== 'all') {
                $counts[$status] = (int) ($tally[$status] ?? 0);
            }
        }

        $query = Order::with(['consumer', 'providerProfile.user', 'items']);
        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('consumer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('providerProfile', fn ($p) => $p->where('business_name', 'like', "%{$search}%"))
                    ->orWhereHas('providerProfile.user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('items', fn ($i) => $i->where('product_name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders', 'counts', 'filter', 'search'));
    }

    public function show(Order $order): View
    {
        $order->load(['consumer', 'providerProfile.user', 'items', 'events.causedBy', 'payments', 'address', 'coupon']);

        return view('admin.orders.show', compact('order'));
    }
}
