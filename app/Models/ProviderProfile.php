<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderProfile extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'business_name',
        'bio',
        'experience_years',
        'rating_avg',
        'reviews_count',
        'city',
        'address',
        'latitude',
        'longitude',
        'cnic_number',
        'payout_method',
        'payout_account_title',
        'payout_account_number',
        'payout_bank_name',
        'status',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'experience_years' => 'integer',
            'rating_avg' => 'decimal:2',
            'reviews_count' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProviderDocument::class);
    }

    public function portfolioPhotos(): HasMany
    {
        return $this->hasMany(ProviderPortfolioPhoto::class)->orderBy('sort_order');
    }

    public function providerServices(): HasMany
    {
        return $this->hasMany(ProviderService::class);
    }

    /** Services this provider offers, via the provider_services pivot. Enables route-model-binding scoping for {provider}/{service} routes. */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'provider_services')
            ->withPivot('price', 'is_active');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function contractItems(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function hasPayoutMethod(): bool
    {
        return filled($this->payout_method) && filled($this->payout_account_number);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isEditable(): bool
    {
        return $this->isDraft() || $this->isRejected();
    }

    public function documentOfType(string $type): ?ProviderDocument
    {
        return $this->documents->firstWhere('type', $type);
    }

    public function offeredService(int $serviceId): ?ProviderService
    {
        return $this->providerServices()
            ->where('service_id', $serviceId)
            ->where('is_active', true)
            ->first();
    }

    /** Recompute the cached rating from the reviews table. */
    public function recomputeRating(): void
    {
        $agg = $this->reviews()
            ->selectRaw('COUNT(*) as c, AVG(rating) as a')
            ->first();

        $count = (int) ($agg->c ?? 0);

        $this->update([
            'reviews_count' => $count,
            'rating_avg' => $count > 0 ? round((float) $agg->a, 2) : 0,
        ]);
    }
}