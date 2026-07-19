<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = [
        'question_en',
        'answer_en',
        'question_ur',
        'answer_ur',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function question(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'ur' && $this->question_ur) {
            return $this->question_ur;
        }

        return $this->question_en;
    }

    public function answer(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($locale === 'ur' && $this->answer_ur) {
            return $this->answer_ur;
        }

        return $this->answer_en;
    }
}
