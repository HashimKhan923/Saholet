<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference',
        'consumer_id',
        'corporate_account_id',
        'title',
        'description',
        'address',
        'latitude',
        'longitude',
        'city',
        'preferred_start_date',
        'status',
        'quoted_total',
        'admin_notes',
        'quoted_by',
        'quoted_at',
        'accepted_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'preferred_start_date' => 'date',
            'quoted_total' => 'decimal:2',
            'quoted_at' => 'datetime',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function corporateAccount(): BelongsTo
    {
        return $this->belongsTo(CorporateAccount::class);
    }

    public function quotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quoted_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ContractPhoto::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ContractMilestone::class)->orderBy('sequence');
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isQuoted(): bool
    {
        return $this->status === self::STATUS_QUOTED;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_QUOTED], true);
    }
}
