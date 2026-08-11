<?php

namespace App\Helpers;

class ChecksheetHelper
{
    /**
     * Format initials string into XX / YY / ZZ format
     */
    public static function formatInitials(?string $value): ?string
    {
        if (is_null($value) || trim($value) === '') {
            return null;
        }

        // Remove non-alphabet characters
        $clean = preg_replace('/[^a-zA-Z]/', '', $value);
        if (empty($clean)) {
            return null;
        }

        $uppercase = strtoupper($clean);
        $chunks = str_split($uppercase, 2);
        return implode(' / ', $chunks);
    }
}
