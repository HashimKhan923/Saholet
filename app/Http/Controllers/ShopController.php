<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProviderProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * The public storefront directory — browse by shop first, then that shop's
 * products. Sits alongside ProductController (which still serves the product
 * detail page and the underlying search/filter query logic this reuses).
 */
class ShopController extends Controller
{
    /** Every approved provider with at least one active product, browsable as a shop card grid. */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $shops = ProviderProfile::approved()
            ->withCount(['products as products_count' => fn ($q) => $q->active()])
            ->whereHas('products', fn ($q) => $q->active())
            ->when(! empty($validated['q']), function ($q) use ($validated) {
                $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $validated['q']) . '%';
                $q->where(fn ($qq) => $qq->where('shop_name', 'like', $term)->orWhere('business_name', 'like', $term));
            })
            ->when(! empty($validated['city']), fn ($q) => $q->where('city', $validated['city']))
            ->orderByRaw('COALESCE(shop_name, business_name)')
            ->paginate(12)
            ->withQueryString();

        return view('shops.index', [
            'shops' => $shops,
            'filters' => $validated,
        ]);
    }

    /** One shop's own storefront — its products, filterable by name and by the categories it actually stocks. */
    public function show(Request $request, ProviderProfile $provider): View
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

        $products = $query->orderByDesc('id')->paginate(15)->withQueryString();

        $categories = Category::active()
            ->whereHas('products', fn ($q) => $q->active()->where('provider_profile_id', $provider->id))
            ->orderBy('name')
            ->get();

        return view('shops.show', [
            'provider' => $provider,
            'products' => $products,
            'categories' => $categories,
            'filters' => $validated,
            'wishlistedIds' => $this->wishlistedIds($request),
        ]);
    }

    /** @return Collection<int, int> */
    private function wishlistedIds(Request $request): Collection
    {
        $user = $request->user();

        return $user && $user->isConsumer() ? $user->wishlistItems()->pluck('product_id') : collect();
    }
}
