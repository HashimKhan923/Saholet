<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_profile_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'discount_price',
        'stock_quantity',
        'sku',
        'is_active',
        'deactivation_reason',
        'reactivation_instructions',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProductPhoto::class)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->latest();
    }

    /** Uses the eager-loaded `withCount('reviews')` aggregate when present (product grids), otherwise queries fresh (product detail page). */
    public function reviewsCount(): int
    {
        if (array_key_exists('reviews_count', $this->attributes)) {
            return (int) $this->attributes['reviews_count'];
        }

        return $this->reviews()->count();
    }

    /** Uses the eager-loaded `withAvg('reviews', 'rating')` aggregate when present (product grids), otherwise queries fresh (product detail page). */
    public function ratingAvg(): ?float
    {
        if (array_key_exists('reviews_avg_rating', $this->attributes)) {
            return $this->attributes['reviews_avg_rating'] !== null ? (float) $this->attributes['reviews_avg_rating'] : null;
        }

        $avg = $this->reviews()->avg('rating');

        return $avg !== null ? (float) $avg : null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function hasDiscount(): bool
    {
        return $this->discount_price !== null && (float) $this->discount_price < (float) $this->price;
    }

    /** What a customer actually pays — the discount price when one's set and genuinely lower, otherwise the regular price. */
    public function effectivePrice(): float
    {
        return $this->hasDiscount() ? (float) $this->discount_price : (float) $this->price;
    }

    public function discountPercentage(): int
    {
        if (! $this->hasDiscount() || (float) $this->price <= 0) {
            return 0;
        }

        return (int) round((1 - ((float) $this->discount_price / (float) $this->price)) * 100);
    }

    public function isPurchasable(): bool
    {
        if (! $this->is_active || ! $this->isInStock()) {
            return false;
        }

        $this->loadMissing('providerProfile');

        return (bool) $this->providerProfile?->isApproved();
    }

    /** Unique per provider (two providers can each have their own "wrench"), like Category::generateSlug(). */
    public static function generateSlug(string $name, int $providerProfileId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $i = 1;

        while (
            static::where('provider_profile_id', $providerProfileId)
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
