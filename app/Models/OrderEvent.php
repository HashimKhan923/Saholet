<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEvent extends Model
{
    public const TYPE_CREATED = 'created';
    public const TYPE_STATUS_CHANGED = 'status_changed';
    public const TYPE_NOTE_ADDED = 'note_added';
    public const TYPE_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'type',
        'from_status',
        'to_status',
        'note',
        'caused_by',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function causedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caused_by');
    }
}
