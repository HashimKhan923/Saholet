<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Invoice extends Model
{
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_QUOTATION = 'quotation';

    protected $fillable = [
        'type',
        'reference',
        'title',
        'invoiceable_type',
        'invoiceable_id',
        'consumer_id',
        'bill_to_name',
        'bill_to_email',
        'bill_to_phone',
        'bill_to_address',
        'total',
        'discount',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'discount' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isQuotation(): bool
    {
        return $this->type === self::TYPE_QUOTATION;
    }

    public static function generateReference(string $type): string
    {
        $prefix = $type === self::TYPE_QUOTATION ? 'QUO' : 'INV';

        do {
            $ref = $prefix . '-' . strtoupper(Str::random(6));
        } while (static::where('reference', $ref)->exists());

        return $ref;
    }
}
