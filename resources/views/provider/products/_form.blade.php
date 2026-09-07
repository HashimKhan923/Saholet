@php
    $product = $product ?? null;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Name</label>
        <input type="text" name="name" required maxlength="255" value="{{ old('name', $product->name ?? '') }}"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <x-field-error name="name" />
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Category <span class="text-slate-400">(optional)</span></label>
        <select name="category_id"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">No category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-field-error name="category_id" />
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">SKU <span class="text-slate-400">(optional)</span></label>
        <input type="text" name="sku" maxlength="100" value="{{ old('sku', $product->sku ?? '') }}"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <x-field-error name="sku" />
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Price (Rs.)</label>
        <input type="number" name="price" required min="0" step="0.01" value="{{ old('price', $product->price ?? '') }}"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <x-field-error name="price" />
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Discount price (Rs.) <span class="text-slate-400">(optional)</span></label>
        <input type="number" name="discount_price" min="0" step="0.01" value="{{ old('discount_price', $product->discount_price ?? '') }}" placeholder="Leave blank if not on sale"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <p class="mt-1 text-xs text-slate-400">Must be lower than the price above — shown crossed out with this sale price next to it.</p>
        <x-field-error name="discount_price" />
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Stock quantity</label>
        <input type="number" name="stock_quantity" required min="0" step="1" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
        <x-field-error name="stock_quantity" />
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Description <span class="text-slate-400">(optional)</span></label>
        <textarea name="description" rows="4" maxlength="5000"
            class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('description', $product->description ?? '') }}</textarea>
        <x-field-error name="description" />
    </div>

    <div class="sm:col-span-2">
        <label class="flex items-center gap-2.5">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))
                class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200 dark:border-slate-600">
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Visible in shop</span>
        </label>
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Photos <span class="text-slate-400">(optional, up to 8)</span></label>
        <div class="mt-1.5">
            <x-photo-picker name="photos" :max="8 - ($product?->photos->count() ?? 0)" />
        </div>
        <x-field-error name="photos" />
    </div>
</div>

@if ($product && $product->photos->isNotEmpty())
    <div class="mt-6">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Current photos</h3>
        <div class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-5">
            @foreach ($product->photos as $photo)
                <div class="group relative aspect-square overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                    <img src="{{ $photo->url() }}" class="h-full w-full object-cover">
                    <form method="POST" action="{{ route('provider.products.photos.destroy', $photo) }}" class="absolute right-1 top-1 opacity-0 transition group-hover:opacity-100">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-900/70 text-white transition hover:bg-red-600" aria-label="Remove photo" onclick="return confirm('Remove this photo?')">
                            <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endif
