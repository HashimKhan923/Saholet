<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class JobPostPhoto extends Model
{
    protected $fillable = [
        'job_post_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
