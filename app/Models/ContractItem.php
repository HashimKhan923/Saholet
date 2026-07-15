<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractItem extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'contract_id',
        'service_id',
        'quantity',
        'notes',
        'quoted_price',
        'status',
        'provider_profile_id',
        'booking_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'quoted_price' => 'decimal:2',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isAssignable(): bool
    {
        return in_array($this->status, [self::STATUS_QUOTED], true) && $this->quoted_price !== null;
    }
}
