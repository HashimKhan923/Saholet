<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /** Public marketplace browse — every purchasable product across every provider's shop. */
    public function index(Request $request): View
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

        $products = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $categories = Category::active()->orderBy('name')->get();

        return view('shop.index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $validated,
            'provider' => ! empty($validated['provider']) ? ProviderProfile::find($validated['provider']) : null,
            'wishlistedIds' => $this->wishlistedIds($request),
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'photos', 'providerProfile.user', 'reviews.user']);
        abort_unless($product->providerProfile?->isApproved(), 404);

        $related = Product::active()
            ->where('provider_profile_id', $product->provider_profile_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        $wishlistedIds = $this->wishlistedIds($request);

        return view('shop.show', [
            'product' => $product,
            'related' => $related,
            'wishlisted' => $wishlistedIds->contains($product->id),
            'wishlistedIds' => $wishlistedIds,
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, int> */
    private function wishlistedIds(Request $request): \Illuminate\Support\Collection
    {
        $user = $request->user();

        return $user && $user->isConsumer() ? $user->wishlistItems()->pluck('product_id') : collect();
    }
}
