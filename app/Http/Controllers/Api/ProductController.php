<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /** Public marketplace browse. Query: q, category (id), city, provider (id). */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'exists:categories,id'],
            'city' => ['nullable', 'string', 'max:100'],
            'provider' => ['nullable', 'exists:provider_profiles,id'],
        ]);

        $query = Product::query()
            ->active()
            ->with(['category', 'photos', 'providerProfile.user'])
            ->whereHas('providerProfile', fn ($q) => $q->approved()
                ->when(! empty($validated['city']), fn ($q) => $q->where('city', $validated['city'])));

        if (! empty($validated['q'])) {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $validated['q']) . '%';
            $query->where('name', 'like', $term);
        }

        if (! empty($validated['category'])) {
            $query->where('category_id', $validated['category']);
        }

        if (! empty($validated['provider'])) {
            $query->where('provider_profile_id', $validated['provider']);
        }

        $products = $query->orderByDesc('id')->paginate(15);

        return response()->json([
            'products' => ProductResource::collection($products->getCollection()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'photos', 'providerProfile.user']);
        abort_unless($product->providerProfile?->isApproved(), 404);

        $related = Product::active()
            ->where('provider_profile_id', $product->provider_profile_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return response()->json([
            'product' => new ProductResource($product),
            'related_products' => ProductResource::collection($related),
        ]);
    }
}
