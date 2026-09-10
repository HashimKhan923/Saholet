<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Moderation only — products publish immediately when a provider adds them
 * (see ProductController@store, Provider namespace), so there's no
 * admin-side create/approve step here, only deactivate/remove after the
 * fact.
 */
class ProductController extends Controller
{
    public function __construct(private Notifier $notifier) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $query = Product::with(['providerProfile.user', 'category'])->latest();

        if ($q !== '') {
            $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            $query->where(fn ($w) => $w->where('name', 'like', $term)
                ->orWhere('sku', 'like', $term)
                ->orWhereHas('providerProfile', fn ($p) => $p->where('business_name', 'like', $term)));
        }

        $products = $query->paginate(20)->withQueryString();

        return view('admin.products.index', compact('products', 'q'));
    }

    public function show(Product $product): View
    {
        $product->load(['providerProfile.user', 'category', 'photos']);

        return view('admin.products.show', compact('product'));
    }

    public function toggleActive(Request $request, Product $product): RedirectResponse
    {
        if ($product->is_active) {
            $data = $request->validate([
                'deactivation_reason' => ['required', 'string', 'max:2000'],
                'reactivation_instructions' => ['required', 'string', 'max:2000'],
            ]);

            $product->update([
                'is_active' => false,
                'deactivation_reason' => $data['deactivation_reason'],
                'reactivation_instructions' => $data['reactivation_instructions'],
            ]);

            $product->loadMissing('providerProfile.user');
            $this->notifier->notify(
                $product->providerProfile->user,
                'product_deactivated',
                'Product deactivated: ' . $product->name,
                $data['deactivation_reason'] . ' — To reactivate: ' . $data['reactivation_instructions'],
                route('provider.products.edit', $product),
                excludeChannels: ['mail']
            );

            return back()->with('success', 'Product deactivated.');
        }

        $product->update([
            'is_active' => true,
            'deactivation_reason' => null,
            'reactivation_instructions' => null,
        ]);

        return back()->with('success', 'Product reactivated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product removed.');
    }
}
