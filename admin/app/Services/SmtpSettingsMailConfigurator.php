<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

/**
 * Applies outbound mail configuration from the `settings` table only.
 * Does not read MAIL_* or other env-based mail config.
 */
final class SmtpSettingsMailConfigurator
{
    public function applyFromDatabase(): void
    {
        $settings = Setting::query()->pluck('value', 'key');

        $host = trim((string) ($settings['smtp_host'] ?? ''));
        $portRaw = trim((string) ($settings['smtp_port'] ?? ''));
        $username = trim((string) ($settings['smtp_username'] ?? ''));
        $passwordEncrypted = (string) ($settings['smtp_password'] ?? '');
        $encryption = strtolower(trim((string) ($settings['smtp_encryption'] ?? '')));
        $fromAddress = trim((string) ($settings['mail_from_address'] ?? ''));
        $fromName = trim((string) ($settings['mail_from_name'] ?? ''));

        $password = '';
        if ($passwordEncrypted !== '') {
            try {
                $password = Crypt::decryptString($passwordEncrypted);
            } catch (\Throwable) {
                $password = '';
            }
        }

        $port = $portRaw !== '' ? (int) $portRaw : 0;

        $complete = $host !== ''
            && $port > 0
            && $username !== ''
            && $password !== ''
            && $fromAddress !== '';

        if ($encryption !== '' && ! in_array($encryption, ['tls', 'ssl'], true)) {
            $encryption = '';
        }

        if ($complete) {
            $encryptionValue = $encryption === '' ? null : $encryption;
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            $localDomain = is_string($appHost) && $appHost !== '' ? $appHost : 'localhost';

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'scheme' => null,
                    'url' => null,
                    'host' => $host,
                    'port' => $port,
                    'encryption' => $encryptionValue,
                    'username' => $username,
                    'password' => $password,
                    'timeout' => null,
                    'local_domain' => $localDomain,
                ],
                'mail.from' => [
                    'address' => $fromAddress,
                    'name' => $fromName !== '' ? $fromName : trim((string) ($settings['company_title'] ?? '')),
                ],
            ]);

            return;
        }

        config([
            'mail.default' => 'array',
            'mail.from' => [
                'address' => $fromAddress,
                'name' => $fromName,
            ],
        ]);
    }
}
