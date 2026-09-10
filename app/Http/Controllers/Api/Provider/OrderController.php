<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private const FILTERS = ['all', 'pending', 'confirmed', 'ready', 'completed', 'cancelled'];

    public function __construct(private OrderFulfillmentService $fulfillment) {}

    public function index(Request $request): JsonResponse
    {
        $profile = $request->user()->providerProfile;

        $filter = (string) $request->query('status', 'all');
        if (! in_array($filter, self::FILTERS, true)) {
            $filter = 'all';
        }

        if (! $profile) {
            return response()->json(['orders' => [], 'counts' => array_fill_keys(self::FILTERS, 0)]);
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

        $orders = $query->latest()->paginate(12);

        return response()->json([
            'orders' => OrderResource::collection($orders->getCollection()),
            'counts' => $counts,
            'pagination' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items', 'consumer', 'events.causedBy', 'payments', 'address', 'coupon']);

        return response()->json(['order' => new OrderResource($order)]);
    }

    public function confirm(Order $order): JsonResponse
    {
        $this->authorize('updateStatus', $order);
        abort_unless($order->isPending(), 422, 'This order is not awaiting confirmation.');

        $this->fulfillment->confirm($order);

        return response()->json(['order' => new OrderResource($order->fresh())]);
    }

    /** Body (delivery orders only): delivery_method — free text, entirely optional. */
    public function markReady(Request $request, Order $order): JsonResponse
    {
        $this->authorize('updateStatus', $order);
        abort_unless($order->isConfirmed(), 422, 'This order is not ready to be marked ready.');

        $data = $request->validate([
            'delivery_method' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->fulfillment->markReady($order, $data['delivery_method'] ?? null);

        return response()->json(['order' => new OrderResource($order->fresh())]);
    }

    public function complete(Order $order): JsonResponse
    {
        $this->authorize('updateStatus', $order);
        abort_unless($order->isReady(), 422, 'This order is not ready to be completed.');

        $this->fulfillment->complete($order);

        return response()->json(['order' => new OrderResource($order->fresh())]);
    }

    /** Body: reason?. */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('updateStatus', $order);
        abort_unless($order->canBeCancelled(), 422, 'This order can no longer be cancelled.');

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $this->fulfillment->cancel($order, $data['reason'] ?? null);

        return response()->json(['order' => new OrderResource($order->fresh())]);
    }
}
