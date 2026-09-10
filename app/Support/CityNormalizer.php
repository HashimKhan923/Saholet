<?php

namespace App\Support;

class CityNormalizer
{
    /**
     * Collapses whitespace and title-cases a free-text city name so "karachi",
     * "KARACHI" and "Karachi " all normalize to the same stored value, instead of
     * showing up as separate entries in city filters.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim(preg_replace('/\s+/', ' ', $value));

        if ($trimmed === '') {
            return $trimmed;
        }

        return mb_convert_case($trimmed, MB_CASE_TITLE, 'UTF-8');
    }
}
