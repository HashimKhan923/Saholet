<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPost extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_AWARDED = 'awarded';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference',
        'consumer_id',
        'service_id',
        'description',
        'budget',
        'preferred_date',
        'address',
        'latitude',
        'longitude',
        'city',
        'status',
        'awarded_at',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'preferred_date' => 'date',
            'awarded_at' => 'datetime',
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

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(JobPostPhoto::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isAwarded(): bool
    {
        return $this->status === self::STATUS_AWARDED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function acceptedBid(): ?Bid
    {
        return $this->bids->firstWhere('status', Bid::STATUS_ACCEPTED);
    }
}