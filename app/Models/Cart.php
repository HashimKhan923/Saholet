<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** Cart items grouped by the provider that owns each product — the shape checkout splits into one order per group.
     * Eager-loads everything ProductResource needs (photos, category) so a cart item's
     * product is a complete representation, same as browsing/wishlist — not just enough
     * to compute shipping. */
    public function itemsByProvider(): Collection
    {
        return $this->items()->with('product.providerProfile.user', 'product.photos', 'product.category')->get()
            ->groupBy(fn (CartItem $item) => $item->product->provider_profile_id);
    }
}
