<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

final class StorefrontUrlHelper
{
    /**
     * Customer-facing site base URL (never APP_URL / API host).
     *
     * @param  array<string, mixed>  $settings  key => value from Setting::pluck
     */
    public static function baseUrl(array $settings): string
    {
        foreach (['frontend_url', 'storefront_url', 'customer_app_url'] as $key) {
            $v = trim((string) ($settings[$key] ?? ''));
            if ($v !== '') {
                return rtrim($v, '/');
            }
        }

        $fromEnv = trim((string) config('app.frontend_url', ''));
        if ($fromEnv !== '') {
            return rtrim($fromEnv, '/');
        }

        $fallback = 'http://127.0.0.1:3000';
        Log::warning('StorefrontUrlHelper: FRONTEND_URL / frontend_url not set; using fallback', [
            'fallback' => $fallback,
        ]);

        return rtrim($fallback, '/');
    }
}
