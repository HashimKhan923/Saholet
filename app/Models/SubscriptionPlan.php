<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'slug',
        'description',
        'frequency_months',
        'total_visits',
        'price_per_visit',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'frequency_months' => 'integer',
            'total_visits' => 'integer',
            'price_per_visit' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public static function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . ++$i;
        }

        return $slug;
    }

    public function frequencyLabel(): string
    {
        return match ($this->frequency_months) {
            1 => 'Monthly',
            3 => 'Every 3 months',
            6 => 'Every 6 months',
            12 => 'Yearly',
            default => 'Every ' . $this->frequency_months . ' months',
        };
    }
}
