<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CorporateAccount extends Model
{
    public const ROLE_OWNER = 'owner';
    public const ROLE_MEMBER = 'member';

    protected $fillable = [
        'name',
        'owner_id',
        'billing_email',
        'billing_phone',
        'address',
        'city',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'corporate_account_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /** Total value of every payment placed by any team member, across bookings, contract milestones, and subscription visits. */
    public function totalSpend(): float
    {
        $memberIds = $this->members()->pluck('id');

        return (float) Payment::whereIn('consumer_id', $memberIds)
            ->whereIn('status', [Payment::STATUS_ESCROW, Payment::STATUS_RELEASED])
            ->sum('amount');
    }
}
