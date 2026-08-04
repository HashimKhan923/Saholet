<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyRequest extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_MATCHED = 'matched';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference',
        'consumer_id',
        'service_id',
        'address',
        'latitude',
        'longitude',
        'city',
        'notes',
        'status',
        'quoted_price',
        'quoted_at',
        'quoted_by',
        'admin_note',
        'accepted_at',
        'declined_at',
        'booking_id',
        'matched_provider_profile_id',
        'matched_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'quoted_price' => 'decimal:2',
            'quoted_at' => 'datetime',
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'matched_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function matchedProvider(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'matched_provider_profile_id');
    }

    public function quotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quoted_by');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeAwaitingQuote(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isQuoted(): bool
    {
        return $this->status === self::STATUS_QUOTED;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isDeclined(): bool
    {
        return $this->status === self::STATUS_DECLINED;
    }

    public function isMatched(): bool
    {
        return $this->status === self::STATUS_MATCHED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
