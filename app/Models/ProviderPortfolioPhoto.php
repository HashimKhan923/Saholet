<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProviderPortfolioPhoto extends Model
{
    protected $fillable = [
        'provider_profile_id',
        'path',
        'original_name',
        'caption',
        'mime_type',
        'size',
        'sort_order',
    ];

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
