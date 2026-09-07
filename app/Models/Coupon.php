<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    public const TYPE_FLAT = 'flat';
    public const TYPE_PERCENTAGE = 'percentage';

    protected $fillable = [
        'provider_profile_id',
        'code',
        'type',
        'value',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isUsable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function alreadyRedeemedBy(User $user): bool
    {
        return $this->redemptions()->where('user_id', $user->id)->exists();
    }

    /** Discount for a given subtotal — never more than the subtotal itself. */
    public function discountFor(float $subtotal): float
    {
        $raw = $this->type === self::TYPE_PERCENTAGE
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($raw, $subtotal), 2);
    }

    public function label(): string
    {
        return $this->type === self::TYPE_PERCENTAGE
            ? rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.') . '% off'
            : 'Rs. ' . number_format((float) $this->value, 0) . ' off';
    }
}
