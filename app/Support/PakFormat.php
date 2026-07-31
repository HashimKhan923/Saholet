<?php

namespace App\Support;

class PakFormat
{
    /** 03XX-XXXXXXX — 4 digits, dash, 7 digits. Returns the input unchanged if it isn't 11 digits. */
    public static function phone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        // Strip a leading country code (92) or trunk zero so re-formatting old data lines up too.
        if (str_starts_with($digits, '92') && strlen($digits) > 11) {
            $digits = '0' . substr($digits, 2);
        }

        if (strlen($digits) !== 11) {
            return $value;
        }

        return substr($digits, 0, 4) . '-' . substr($digits, 4);
    }

    /** XXXXX-XXXXXXX-X — 5 digits, dash, 7 digits, dash, 1 digit. Returns the input unchanged if it isn't 13 digits. */
    public static function cnic(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        if (strlen($digits) !== 13) {
            return $value;
        }

        return substr($digits, 0, 5) . '-' . substr($digits, 5, 7) . '-' . substr($digits, 12, 1);
    }
}
