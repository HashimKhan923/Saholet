<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyRequest extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_MATCHED = 'matched';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference',
        'consumer_id',
        'service_id',
        'address',
        'city',
        'notes',
        'status',
        'booking_id',
        'matched_provider_profile_id',
        'matched_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
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

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
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