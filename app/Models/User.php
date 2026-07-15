<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_CONSUMER = 'consumer';
    public const ROLE_PROVIDER = 'provider';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_JOB_SEEKER = 'job_seeker';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'password',
        'suspended_at',
        'referral_code',
        'referred_by',
        'credit_balance',
        'corporate_account_id',
        'corporate_role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'password' => 'hashed',
            'credit_balance' => 'decimal:2',
        ];
    }

    public function providerProfile(): HasOne
    {
        return $this->hasOne(ProviderProfile::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'consumer_id');
    }

    public function jobSeekerProfile(): HasOne
    {
        return $this->hasOne(JobSeekerProfile::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class)->orderByDesc('is_default')->latest();
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'consumer_id');
    }

    public function referrer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function referralRewardsGiven(): HasMany
    {
        return $this->hasMany(ReferralReward::class, 'referrer_id');
    }

    public function corporateAccount(): BelongsTo
    {
        return $this->belongsTo(CorporateAccount::class);
    }

    public function ownedCorporateAccount(): HasOne
    {
        return $this->hasOne(CorporateAccount::class, 'owner_id');
    }

    public function isCorporateOwner(): bool
    {
        return $this->corporate_account_id !== null && $this->corporate_role === CorporateAccount::ROLE_OWNER;
    }

    public static function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    public function isConsumer(): bool
    {
        return $this->role === self::ROLE_CONSUMER;
    }

    public function isProvider(): bool
    {
        return $this->role === self::ROLE_PROVIDER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isJobSeeker(): bool
    {
        return $this->role === self::ROLE_JOB_SEEKER;
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    /** Admins can never be suspended. */
    public function canBeSuspended(): bool
    {
        return ! $this->isAdmin();
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'admin.dashboard',
            self::ROLE_PROVIDER => 'provider.dashboard',
            self::ROLE_JOB_SEEKER => 'job-seeker.dashboard',
            default => 'consumer.dashboard',
        };
    }
}