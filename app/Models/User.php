<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_CONSUMER = 'consumer';
    public const ROLE_PROVIDER = 'provider';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_JOB_SEEKER = 'job_seeker';
    public const ROLE_STAFF = 'staff';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'role',
        'permissions',
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

    protected $appends = [
        'avatar_url',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? Storage::disk('public')->url($this->avatar) : null;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'password' => 'hashed',
            'credit_balance' => 'decimal:2',
            'permissions' => 'array',
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

    public function careerApplications(): HasMany
    {
        return $this->hasMany(CareerApplication::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class)->orderByDesc('is_default')->latest();
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
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

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    /**
     * Admins can do everything. Staff can only do what their `permissions`
     * JSON grants them for the given admin module. Everyone else, nothing.
     */
    public function hasPermission(string $module, string $action = 'view'): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->isStaff()) {
            return false;
        }

        return in_array($action, $this->permissions[$module] ?? [], true);
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

    /** True once the account has gone through anonymized deletion — {@see canBeDeleted()}. */
    public function isDeleted(): bool
    {
        return str_starts_with((string) $this->email, 'deleted-');
    }

    /** Admins can never be deleted. */
    public function canBeDeleted(): bool
    {
        return ! $this->isAdmin();
    }

    /**
     * Wipes personally-identifying fields, disables login, and revokes tokens —
     * without hard-deleting the row, since every booking/payment/review/dispute
     * FK cascades from users and would otherwise be destroyed with it.
     *
     * Job seekers are the one exception: their profile (resume, skills, bio,
     * address, qualification) isn't transactional history anyone downstream
     * needs — it's just PII sitting there with nothing referencing it, so it
     * gets wiped outright rather than kept around like a booking would be.
     */
    public function anonymize(): void
    {
        if ($this->avatar) {
            Storage::disk('public')->delete($this->avatar);
        }

        if ($this->role === self::ROLE_JOB_SEEKER) {
            $this->wipeJobSeekerData();
        }

        $this->update([
            'name' => 'Deleted User',
            'email' => 'deleted-' . $this->id . '@sahoulat.local',
            'phone' => null,
            'avatar' => null,
            'password' => Hash::make(Str::random(40)),
            'suspended_at' => now(),
        ]);

        $this->tokens()->delete();
    }

    private function wipeJobSeekerData(): void
    {
        if ($profile = $this->jobSeekerProfile) {
            $profile->deleteResumeFile();
            $profile->delete();
        }

        // Each application keeps its own copied resume (separate from the profile's),
        // per-application per the apply flow — its DB column is required (not-null),
        // so the file content is wiped but the row and status/timeline history stay,
        // same "keep the record, drop the PII" treatment as bookings get elsewhere.
        $disk = config('careers.disk');
        $this->careerApplications()->get()->each(function (CareerApplication $application) use ($disk) {
            if ($application->resume_path) {
                Storage::disk($disk)->delete($application->resume_path);
            }
            $application->update(['cover_letter' => null]);
        });
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN, self::ROLE_STAFF => 'admin.dashboard',
            self::ROLE_PROVIDER => 'provider.dashboard',
            self::ROLE_JOB_SEEKER => 'job-seeker.dashboard',
            default => 'consumer.dashboard',
        };
    }

}