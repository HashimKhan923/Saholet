<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\ProductReview;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderFulfillmentService $fulfillment) {}

    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['items', 'providerProfile.user'])
            ->where('consumer_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return response()->json([
            'orders' => OrderResource::collection($orders->getCollection()),
            'pagination' => ['current_page' => $orders->currentPage(), 'last_page' => $orders->lastPage(), 'total' => $orders->total()],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['items', 'providerProfile.user', 'events.causedBy', 'payments', 'address', 'coupon']);

        return response()->json(['order' => new OrderResource($order)]);
    }

    /** Body: reason?. */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);
        abort_unless($order->canBeCancelled(), 422, 'This order can no longer be cancelled.');

        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $this->fulfillment->cancel($order, $data['reason'] ?? null);

        return response()->json(['order' => new OrderResource($order->fresh())]);
    }

    /** Body: product_id, rating, comment?. One review per product per order. */
    public function storeReview(Request $request, Order $order): JsonResponse
    {
        $this->authorize('review', $order);
        abort_unless($order->isCompleted(), 422, 'You can only review products from a completed order.');

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $reviewable = $order->reviewableItems()->pluck('product_id');
        abort_unless($reviewable->contains((int) $data['product_id']), 422, 'This product isn\'t open for review on this order.');

        $review = ProductReview::create([
            'product_id' => $data['product_id'],
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return response()->json(['review' => $review], 201);
    }
}
