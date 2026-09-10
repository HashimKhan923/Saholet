<?php

namespace App\Http\Controllers\Api\Consumer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::whereIn('id', $request->user()->wishlistItems()->pluck('product_id'))
            ->with(['category', 'photos', 'providerProfile.user'])
            ->latest()
            ->paginate(16);

        return response()->json([
            'products' => ProductResource::collection($products->getCollection()),
            'pagination' => ['current_page' => $products->currentPage(), 'last_page' => $products->lastPage(), 'total' => $products->total()],
        ]);
    }

    /** Body: product_id. Adds if not already wishlisted, removes if it is. */
    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $user = $request->user();
        $existing = $user->wishlistItems()->where('product_id', $data['product_id'])->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['wishlisted' => false, 'message' => 'Removed from wishlist.']);
        }

        $user->wishlistItems()->create(['product_id' => $data['product_id']]);

        return response()->json(['wishlisted' => true, 'message' => 'Added to wishlist.'], 201);
    }
}
