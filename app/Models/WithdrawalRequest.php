<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_AWAITING_CONFIRMATION = 'awaiting_confirmation';
    public const STATUS_PAID = 'paid';
    public const STATUS_REJECTED = 'rejected';

    public const METHOD_BANK = 'bank';
    public const METHOD_JAZZCASH = 'jazzcash';
    public const METHOD_EASYPAISA = 'easypaisa';

    public const FULFILLMENT_CASH_PICKUP = 'cash_pickup';
    public const FULFILLMENT_BANK_TRANSFER = 'bank_transfer';

    protected $fillable = [
        'reference',
        'provider_profile_id',
        'wallet_id',
        'amount',
        'status',
        'payout_method',
        'payout_account_title',
        'payout_account_number',
        'payout_bank_name',
        'fulfillment_method',
        'screenshot_path',
        'admin_notes',
        'processed_by',
        'processed_at',
        'provider_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
            'provider_confirmed_at' => 'datetime',
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAwaitingConfirmation(): bool
    {
        return $this->status === self::STATUS_AWAITING_CONFIRMATION;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function methodLabel(): string
    {
        return match ($this->payout_method) {
            self::METHOD_BANK => 'Bank transfer',
            self::METHOD_JAZZCASH => 'JazzCash',
            self::METHOD_EASYPAISA => 'Easypaisa',
            default => ucfirst($this->payout_method),
        };
    }
}
