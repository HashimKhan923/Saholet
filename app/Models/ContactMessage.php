<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'mail_sent',
    ];

    protected function casts(): array
    {
        return [
            'mail_sent' => 'boolean',
            'read_at' => 'datetime',
        ];
    }
}
