<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerApplication extends Model
{
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_SHORTLISTED = 'shortlisted';
    public const STATUS_INTERVIEW = 'interview';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_HIRED = 'hired';
    public const STATUS_WITHDRAWN = 'withdrawn';

    public const ACTIVE_STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_SHORTLISTED,
        self::STATUS_INTERVIEW,
    ];

    protected $fillable = [
        'career_listing_id',
        'user_id',
        'resume_path',
        'resume_original_name',
        'cover_letter',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(CareerListing::class, 'career_listing_id');
    }

    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isWithdrawable(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }
}
