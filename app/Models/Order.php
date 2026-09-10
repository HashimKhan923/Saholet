<?php

namespace App\Models;

use App\Concerns\NormalizesCity;
use App\Contracts\Payable;
use App\Services\CommissionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Order extends Model implements Payable
{
    use HasFactory, NormalizesCity;

    protected string $cityColumn = 'shipping_city';

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_READY = 'ready';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_READY,
    ];

    public const FULFILLMENT_DELIVERY = 'delivery';
    public const FULFILLMENT_PICKUP = 'pickup';

    protected $fillable = [
        'reference',
        'consumer_id',
        'provider_profile_id',
        'fulfillment_method',
        'address_id',
        'shipping_address',
        'shipping_city',
        'shipping_lat',
        'shipping_lng',
        'subtotal',
        'coupon_id',
        'discount_amount',
        'shipping_amount',
        'total_amount',
        'payment_method',
        'status',
        'delivery_method',
        'tracking_reference',
        'commission_rate',
        'commission_amount',
        'provider_amount',
        'cancel_reason',
        'ready_at',
        'completed_at',
        'cancelled_at',
        'review_reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_lat' => 'decimal:7',
            'shipping_lng' => 'decimal:7',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'provider_amount' => 'decimal:2',
            'ready_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'review_reminder_sent_at' => 'datetime',
        ];
    }

    /**
     * The provider's product-sale rate, set by an admin — separate from their
     * booking commission_rate. A last-resort default only covers a provider
     * somehow left without one.
     */
    public function commissionRate(): float
    {
        $this->loadMissing('providerProfile');

        return $this->providerProfile?->product_commission_rate !== null
            ? (float) $this->providerProfile->product_commission_rate
            : CommissionService::DEFAULT_RATE;
    }

    public function referenceLabel(): string
    {
        return 'order ' . $this->reference;
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OrderEvent::class)->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function activePayment(): ?Payment
    {
        return $this->payments
            ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_ESCROW, Payment::STATUS_RELEASED])
            ->sortByDesc('id')
            ->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function isDelivery(): bool
    {
        return $this->fulfillment_method === self::FULFILLMENT_DELIVERY;
    }

    public function isPickup(): bool
    {
        return $this->fulfillment_method === self::FULFILLMENT_PICKUP;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isReady(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /** Either side may cancel while nothing has been dispatched/set aside yet. */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED], true);
    }

    public function isParticipant(User $user): bool
    {
        if ($this->consumer_id === $user->id) {
            return true;
        }

        $this->loadMissing('providerProfile');

        return $this->providerProfile && $this->providerProfile->user_id === $user->id;
    }

    public function isProviderUser(User $user): bool
    {
        $this->loadMissing('providerProfile');

        return $this->providerProfile && $this->providerProfile->user_id === $user->id;
    }

    /** Line items on a completed order that don't have a review against this order yet. */
    public function reviewableItems(): Collection
    {
        if (! $this->isCompleted()) {
            return collect();
        }

        $this->loadMissing('items.product', 'reviews');

        $reviewedProductIds = $this->reviews->pluck('product_id');

        return $this->items->filter(fn (OrderItem $item) => $item->product && ! $reviewedProductIds->contains($item->product_id))->values();
    }
}
