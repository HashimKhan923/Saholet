<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    public const STATUS_PENDING_ASSIGNMENT = 'pending_assignment';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'reference',
        'subscription_plan_id',
        'consumer_id',
        'corporate_account_id',
        'provider_profile_id',
        'address',
        'latitude',
        'longitude',
        'city',
        'status',
        'next_visit_date',
        'visits_used',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'next_visit_date' => 'date',
            'visits_used' => 'integer',
            'cancelled_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function corporateAccount(): BelongsTo
    {
        return $this->belongsTo(CorporateAccount::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isPendingAssignment(): bool
    {
        return $this->status === self::STATUS_PENDING_ASSIGNMENT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING_ASSIGNMENT, self::STATUS_ACTIVE], true);
    }

    public function hasVisitsRemaining(): bool
    {
        $total = $this->plan->total_visits;

        return $total === null || $this->visits_used < $total;
    }
}
