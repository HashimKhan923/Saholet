<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ESCROW = 'escrow';
    public const STATUS_RELEASED = 'released';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'reference',
        'booking_id',
        'contract_milestone_id',
        'consumer_id',
        'gateway',
        'amount',
        'credit_applied',
        'commission_rate',
        'commission_amount',
        'provider_amount',
        'status',
        'gateway_reference',
        'paid_at',
        'released_at',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'credit_applied' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'provider_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'released_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function contractMilestone(): BelongsTo
    {
        return $this->belongsTo(ContractMilestone::class);
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    /** What actually needs to go through a payment gateway, after referral credit. */
    public function chargeAmount(): float
    {
        return max(0.0, (float) $this->amount - (float) $this->credit_applied);
    }

    public function isFullyCoveredByCredit(): bool
    {
        return $this->chargeAmount() <= 0.0;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isEscrow(): bool
    {
        return $this->status === self::STATUS_ESCROW;
    }

    public function isReleased(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }
}