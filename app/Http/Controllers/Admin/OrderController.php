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

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders', 'counts', 'filter'));
    }

    public function show(Order $order): View
    {
        $order->load(['consumer', 'providerProfile.user', 'items', 'events.causedBy', 'payments', 'address', 'coupon']);

        return view('admin.orders.show', compact('order'));
    }
}
