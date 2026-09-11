<?php

namespace App\Http\Controllers\Api\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductPhoto;
use App\Models\ProviderProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    private const MAX_PHOTOS = 8;

    public function index(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);
        $products = $profile->products()->with(['category', 'photos'])->orderBy('name')->get();

        return response()->json(['products' => ProductResource::collection($products)]);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $this->profileFor($request);
        $this->authorize('update', $product);

        return response()->json(['product' => new ProductResource($product->load(['category', 'photos']))]);
    }

    /** Body: category_id?, name, description?, price, stock_quantity, sku?, is_active?, photos[]?. */
    public function store(Request $request): JsonResponse
    {
        $profile = $this->profileFor($request);

        abort_unless($profile->canSellProducts(), 422, 'Set up delivery or pickup in your shop settings before adding products.');

        $data = $this->validateData($request);

        $product = $profile->products()->create([
            ...$data,
            'slug' => Product::generateSlug($data['name'], $profile->id),
        ]);

        $this->storePhotos($request, $product);

        return response()->json(['product' => new ProductResource($product->load(['category', 'photos']))], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $this->profileFor($request);
        $this->authorize('update', $product);

        $data = $this->validateData($request, $product);

        $product->update([
            ...$data,
            'slug' => $data['name'] !== $product->name
                ? Product::generateSlug($data['name'], $product->provider_profile_id, $product->id)
                : $product->slug,
        ]);

        $newPhotos = $this->storePhotos($request, $product);
        $this->applyPhotoOrder($request, $product, $newPhotos);

        return response()->json(['product' => new ProductResource($product->fresh(['category', 'photos']))]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        $this->profileFor($request);
        $this->authorize('delete', $product);

        foreach ($product->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        $product->delete();

        return response()->json(['message' => 'Product removed from your shop.']);
    }

    public function destroyPhoto(Request $request, ProductPhoto $photo): JsonResponse
    {
        $this->profileFor($request);
        $this->authorize('delete', $photo);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return response()->json(['message' => 'Photo removed.']);
    }

    /** @return ProductPhoto[] the newly created photos, in upload order (index matters — applyPhotoOrder's "new:N" tokens refer to this order). */
    private function storePhotos(Request $request, Product $product): array
    {
        if (! $request->hasFile('photos')) {
            return [];
        }

        $remaining = self::MAX_PHOTOS - $product->photos()->count();
        if ($remaining <= 0) {
            return [];
        }

        $nextSort = (int) ($product->photos()->max('sort_order') ?? 0);
        $created = [];

        foreach (array_slice($request->file('photos', []), 0, $remaining) as $photo) {
            $path = $photo->store("products/{$product->id}", 'public');

            $created[] = $product->photos()->create([
                'path' => $path,
                'original_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getClientMimeType(),
                'size' => $photo->getSize(),
                'sort_order' => ++$nextSort,
            ]);
        }

        return $created;
    }

    /**
     * `photo_order[]` (optional) is the final on-screen order from the mobile app's drag-to-reorder
     * grid — a mix of existing photo IDs (as numeric strings) and, for photos uploaded in this same
     * request, the token "new:{N}" where N is that photo's index in the `photos[]` upload array
     * (see storePhotos's return order). Without this, a provider dragging an existing photo to the
     * front (to make it the cover) saw the reorder revert after saving, since sort_order was
     * otherwise only ever set once, at upload time, and never touched again.
     */
    private function applyPhotoOrder(Request $request, Product $product, array $newPhotos): void
    {
        $order = $request->input('photo_order');
        if (! is_array($order) || empty($order)) {
            return;
        }

        $existingIds = $product->photos()->pluck('id')->all();

        $sort = 0;
        foreach ($order as $token) {
            $photoId = null;

            if (is_string($token) && str_starts_with($token, 'new:')) {
                $index = (int) substr($token, 4);
                $photoId = $newPhotos[$index]->id ?? null;
            } elseif (is_numeric($token) && in_array((int) $token, $existingIds, true)) {
                $photoId = (int) $token;
            }

            if ($photoId !== null) {
                ProductPhoto::where('id', $photoId)->where('product_id', $product->id)->update(['sort_order' => ++$sort]);
            }
        }
    }

    private function validateData(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:999999'],
            'sku' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'photos' => ['nullable', 'array', 'max:' . self::MAX_PHOTOS],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'photo_order' => ['nullable', 'array', 'max:' . self::MAX_PHOTOS],
            'photo_order.*' => ['string'],
        ]);

        unset($data['photos'], $data['photo_order']);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    private function profileFor(Request $request): ProviderProfile
    {
        Gate::authorize('actAsApprovedProvider');

        return $request->user()->providerProfile;
    }
}
