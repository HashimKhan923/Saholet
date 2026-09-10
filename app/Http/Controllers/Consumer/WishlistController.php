<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::whereIn('id', $request->user()->wishlistItems()->pluck('product_id'))
            ->with(['category', 'photos', 'providerProfile.user'])
            ->latest()
            ->paginate(16)
            ->withQueryString();

        return view('consumer.wishlist.index', compact('products'));
    }

    /** Body: product_id. Adds if not already wishlisted, removes if it is — one button, one route. */
    public function toggle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $user = $request->user();
        $existing = $user->wishlistItems()->where('product_id', $data['product_id'])->first();

        if ($existing) {
            $existing->delete();
            $message = 'Removed from wishlist.';
        } else {
            $user->wishlistItems()->create(['product_id' => $data['product_id']]);
            $message = 'Added to wishlist.';
        }

        return back()->with('success', $message);
    }
}
