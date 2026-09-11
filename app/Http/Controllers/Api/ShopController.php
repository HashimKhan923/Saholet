<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProviderProfileResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** The public storefront directory — browse by shop first, then that shop's products. */
class ShopController extends Controller
{
    /** Every approved provider with at least one active product. Query: q. */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $shops = ProviderProfile::approved()
            ->with('user:id,name,avatar')
            ->withCount(['products as products_count' => fn ($q) => $q->active()])
            ->whereHas('products', fn ($q) => $q->active())
            ->when(! empty($validated['q']), function ($q) use ($validated) {
                $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $validated['q']) . '%';
                $q->where(fn ($qq) => $qq->where('shop_name', 'like', $term)->orWhere('business_name', 'like', $term));
            })
            ->when(! empty($validated['city']), fn ($q) => $q->where('city', $validated['city']))
            ->orderByRaw('COALESCE(shop_name, business_name)')
            ->paginate(12);

        return response()->json([
            'shops' => ProviderProfileResource::collection($shops->getCollection()),
            'pagination' => [
                'current_page' => $shops->currentPage(),
                'last_page' => $shops->lastPage(),
                'total' => $shops->total(),
            ],
        ]);
    }

    /** One shop's own storefront. Query: q, category (id) — scoped to categories that shop actually stocks. */
    public function show(Request $request, ProviderProfile $provider): JsonResponse
    {
        abort_unless($provider->isApproved(), 404);
        abort_unless($provider->products()->active()->exists(), 404);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'exists:categories,id'],
        ]);

        $query = Product::active()
            ->with(['category', 'photos', 'providerProfile.user'])
            ->where('provider_profile_id', $provider->id);

        if (! empty($validated['q'])) {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $validated['q']) . '%';
            $query->where('name', 'like', $term);
        }

        if (! empty($validated['category'])) {
            $query->where('category_id', $validated['category']);
        }

        $products = $query->orderByDesc('id')->paginate(15);

        $categories = Category::active()
            ->whereHas('products', fn ($q) => $q->active()->where('provider_profile_id', $provider->id))
            ->orderBy('name')
            ->get();

        $provider->loadMissing('user:id,name,avatar');

        return response()->json([
            'shop' => new ProviderProfileResource($provider),
            'categories' => CategoryResource::collection($categories),
            'products' => ProductResource::collection($products->getCollection()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }
}
