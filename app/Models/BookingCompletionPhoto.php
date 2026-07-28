<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BookingCompletionPhoto extends Model
{
    protected $fillable = [
        'booking_id',
        'type',
        'path',
        'original_name',
        'mime_type',
        'size',
        'sort_order',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
