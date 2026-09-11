<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderFulfillmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const FILTERS = ['all', 'pending', 'confirmed', 'ready', 'completed', 'cancelled'];

    public function __construct(private OrderFulfillmentService $fulfillment) {}

    public function index(Request $request): View
    {
        $profile = $request->user()->providerProfile;

        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, self::FILTERS, true)) {
            $filter = 'all';
        }

        $search = trim((string) $request->query('q', ''));

        if (! $profile) {
            return view('provider.orders.index', [
                'orders' => Order::whereRaw('1 = 0')->paginate(12),
                'counts' => array_fill_keys(self::FILTERS, 0),
                'filter' => $filter,
                'search' => $search,
            ]);
        }

        $tally = Order::where('provider_profile_id', $profile->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = ['all' => (int) $tally->sum()];
        foreach (self::FILTERS as $status) {
            if ($status !== 'all') {
                $counts[$status] = (int) ($tally[$status] ?? 0);
            }
        }

        $query = Order::with(['consumer', 'items'])->where('provider_profile_id', $profile->id);
        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        if ($search !== '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('consumer', fn (Builder $c) => $c->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('items', fn (Builder $i) => $i->where('product_name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->paginate(12)->withQueryString();

        return view('provider.orders.index', compact('orders', 'counts', 'filter', 'search'));
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items', 'consumer', 'events.causedBy', 'payments', 'address', 'coupon']);

        return view('provider.orders.show', compact('order'));
    }

    public function confirm(Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);
        abort_unless($order->isPending(), 422, 'This order is not awaiting confirmation.');

        $this->fulfillment->confirm($order);

        return back()->with('success', 'Order confirmed.');
    }

    /** Body (delivery orders only): delivery_method — free text, entirely optional. */
    public function markReady(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);
        abort_unless($order->isConfirmed(), 422, 'This order is not ready to be marked ready.');

        $data = $request->validate([
            'delivery_method' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->fulfillment->markReady($order, $data['delivery_method'] ?? null);

        return back()->with('success', $order->isPickup() ? 'Order marked ready for pickup.' : 'Order marked as dispatched.');
    }

    public function complete(Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);
        abort_unless($order->isReady(), 422, 'This order is not ready to be completed.');

        $this->fulfillment->complete($order);

        return back()->with('success', 'Order completed.');
    }

    /** Body: reason?. */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('updateStatus', $order);
        abort_unless($order->canBeCancelled(), 422, 'This order can no longer be cancelled.');

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $this->fulfillment->cancel($order, $data['reason'] ?? null);

        return back()->with('success', 'Order cancelled.');
    }
}
