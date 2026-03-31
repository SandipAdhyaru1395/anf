<?php

namespace App\Support;

/**
 * Normalizes phone/contact values: trim, strip spaces, remove +91 / 91 country prefix, keep alphanumeric only.
 */
final class PhoneNormalizer
{
    public static function normalize(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $s = trim($input);
        if ($s === '') {
            return null;
        }

        // Remove all whitespace (space, tab, NBSP, etc.)
        $s = preg_replace('/\s+/u', '', $s);

        // Drop leading +
        $s = ltrim($s, '+');

        // Remove India country code variants
        if (str_starts_with($s, '0091')) {
            $s = substr($s, 4);
        } elseif (str_starts_with($s, '91') && strlen($s) >= 12) {
            // e.g. 919876543210 -> 9876543210
            $s = substr($s, 2);
        }

        // Remove any remaining non-alphanumeric (dashes, dots, parens)
        $s = preg_replace('/[^a-zA-Z0-9]/', '', $s);

        return $s === '' ? null : $s;
    }
}
