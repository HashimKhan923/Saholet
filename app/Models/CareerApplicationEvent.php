<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerApplicationEvent extends Model
{
    public const TYPE_SUBMITTED = 'submitted';
    public const TYPE_STATUS_CHANGED = 'status_changed';
    public const TYPE_NOTE_ADDED = 'note_added';
    public const TYPE_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'career_application_id',
        'type',
        'from_status',
        'to_status',
        'note',
        'caused_by',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(CareerApplication::class, 'career_application_id');
    }

    public function causedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caused_by');
    }
}
