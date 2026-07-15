<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    public const KEY = 'catalog.public.v1';
    public const TTL = 3600; // 1 hour

    /** Cached active categories + their active services for the public listing. */
    public function categories()
    {
        return Cache::remember(self::KEY, self::TTL, function () {
            return Category::active()
                ->whereHas('services', fn ($q) => $q->where('is_active', true))
                ->with(['services' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        });
    }

    public function flush(): void
    {
        Cache::forget(self::KEY);
    }
}