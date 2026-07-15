<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContractMilestone extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ESCROW = 'escrow';
    public const STATUS_RELEASED = 'released';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'contract_id',
        'title',
        'description',
        'amount',
        'sequence',
        'status',
        'gateway',
        'gateway_reference',
        'paid_at',
        'released_at',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'sequence' => 'integer',
            'paid_at' => 'datetime',
            'released_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
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

    /** Payable once the consumer has accepted the contract's quote (or work is already underway). */
    public function isPayable(): bool
    {
        if (! $this->isPending()) {
            return false;
        }

        $this->loadMissing('contract');

        return in_array($this->contract->status, [Contract::STATUS_ACCEPTED, Contract::STATUS_IN_PROGRESS], true);
    }
}
