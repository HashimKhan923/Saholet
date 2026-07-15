<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDocument extends Model
{
    protected $fillable = [
        'provider_profile_id',
        'type',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function label(): string
    {
        return config("kyc.documents.{$this->type}.label", ucfirst(str_replace('_', ' ', $this->type)));
    }
}