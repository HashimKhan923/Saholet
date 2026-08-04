<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class Booking extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_IN_PROGRESS,
    ];

    protected $fillable = [
        'reference',
        'consumer_id',
        'provider_profile_id',
        'service_id',
        'contract_item_id',
        'subscription_id',
        'corporate_account_id',
        'scheduled_date',
        'scheduled_time',
        'price',
        'duration_minutes',
        'address',
        'latitude',
        'longitude',
        'notes',
        'status',
        'source',
        'cancelled_by',
        'cancellation_reason',
        'completion_notes',
        'confirmed_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'visit_charge_amount',
        'visit_charge_method',
        'visit_charge_screenshot_path',
        'visit_charge_collected_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'confirmed_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'visit_charge_amount' => 'decimal:2',
            'visit_charge_collected_at' => 'datetime',
        ];
    }

    /** Provider kept the whole thing — never touches commission or the platform wallet. */
    public function hasVisitChargeCollected(): bool
    {
        return $this->visit_charge_collected_at !== null;
    }

    public function visitChargeScreenshotUrl(): ?string
    {
        return $this->visit_charge_screenshot_path
            ? Storage::disk('public')->url($this->visit_charge_screenshot_path)
            : null;
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function contractItem(): BelongsTo
    {
        return $this->belongsTo(ContractItem::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function corporateAccount(): BelongsTo
    {
        return $this->belongsTo(CorporateAccount::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function trackingUpdates(): HasMany
    {
        return $this->hasMany(TrackingUpdate::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    public function completionPhotos(): HasMany
    {
        return $this->hasMany(BookingCompletionPhoto::class)->orderBy('sort_order');
    }

    public function beforePhotos(): HasMany
    {
        return $this->hasMany(BookingCompletionPhoto::class)->where('type', 'before')->orderBy('sort_order');
    }

    public function afterPhotos(): HasMany
    {
        return $this->hasMany(BookingCompletionPhoto::class)->where('type', 'after')->orderBy('sort_order');
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

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeCancelledByConsumer(): bool
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

    public function isCommunicable(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    /**
     * Only while `confirmed` — the point of live tracking is showing the
     * provider's route/ETA to the job. Once `in_progress` they've arrived,
     * so there's nothing left to route to and sharing auto-stops.
     */
    public function canShareLocation(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isPayable(): bool
    {
        if (! in_array($this->status, self::ACTIVE_STATUSES, true)) {
            return false;
        }

        return $this->activePayment() === null;
    }

    /** Cash / bank-transfer settlement only ever happens after the job is done. */
    public function needsCompletionPayment(): bool
    {
        return $this->isCompleted() && $this->activePayment() === null;
    }

    /** Consumer may review a completed booking once. */
    public function isReviewable(): bool
    {
        if (! $this->isCompleted()) {
            return false;
        }

        $this->loadMissing('review');

        return $this->review === null;
    }

    /** A dispute may be opened on an agreed/completed booking, once. */
    public function isDisputable(): bool
    {
        if (! in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_IN_PROGRESS, self::STATUS_COMPLETED], true)) {
            return false;
        }

        $this->loadMissing('dispute');

        return $this->dispute === null;
    }

    public function hasOpenDispute(): bool
    {
        $this->loadMissing('dispute');

        return $this->dispute && $this->dispute->isOpen();
    }

    public function dateLabel(): string
    {
        return $this->scheduled_date->format('D, d M Y');
    }

    public function timeLabel(): string
    {
        return Carbon::parse($this->scheduled_time)->format('g:i A');
    }
}