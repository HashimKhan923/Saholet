<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DisputePhoto extends Model
{
    protected $fillable = [
        'dispute_id',
        'path',
        'original_name',
        'mime_type',
        'size',
        'sort_order',
    ];

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
