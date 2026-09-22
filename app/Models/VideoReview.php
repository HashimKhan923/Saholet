<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VideoReview extends Model
{
    protected $fillable = [
        'title',
        'description',
        'youtube_url',
        'youtube_id',
        'sort_order',
        'is_active',
    ];

    protected $appends = [
        'thumbnail_url',
        'embed_url',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getThumbnailUrlAttribute(): string
    {
        return "https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg";
    }

    public function getEmbedUrlAttribute(): string
    {
        // youtube-nocookie.com's embed is stricter about matching the request's
        // origin and throws "Error 153" for videos that work fine on this domain
        // — the regular embed host doesn't have that problem.
        return "https://www.youtube.com/embed/{$this->youtube_id}?autoplay=1&rel=0";
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    /** Pulls the 11-char video id out of any common YouTube URL shape (watch, youtu.be, shorts, embed). */
    public static function extractYoutubeId(string $url): ?string
    {
        $pattern = '~(?:youtube(?:-nocookie)?\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})~i';

        return preg_match($pattern, $url, $matches) ? $matches[1] : null;
    }
}
