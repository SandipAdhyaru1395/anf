<?php

namespace App\Support;

/**
 * From / reply display values from settings rows only (no config/env fallbacks).
 */
final class MailSettingsHelper
{
    /**
     * @param  array<string, mixed>  $settings  key => value from Setting::pluck
     */
    public static function fromAddress(array $settings): string
    {
        $a = trim((string) ($settings['mail_from_address'] ?? ''));
        if ($a !== '') {
            return $a;
        }

        return trim((string) ($settings['company_email'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public static function fromName(array $settings): string
    {
        $n = trim((string) ($settings['mail_from_name'] ?? ''));
        if ($n !== '') {
            return $n;
        }

        return trim((string) ($settings['company_title'] ?? ''));
    }
}
