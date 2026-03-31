<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Setting;
use App\Support\MailSettingsHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerPasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Customer $customer;

    public string $token;

    public function __construct(Customer $customer, string $token)
    {
        $this->customer = $customer;
        $this->token = $token;
    }

    public function build()
    {
        $settings = Setting::query()->pluck('value', 'key')->toArray();
        $companyTitle = (string) ($settings['company_title'] ?? config('app.name'));
        $companyEmail = MailSettingsHelper::fromAddress($settings);
        $fromName = MailSettingsHelper::fromName($settings);

        $name = (string) ($this->customer->company_name ?? 'Customer');
        $email = (string) ($this->customer->email ?? '');
        $base = $this->resolveStorefrontBaseUrl($settings);
        $resetUrl = $base . '/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $email,
        ]);

        $expires = (int) config('auth.passwords.customers.expire', 60);

        $mail = $this
            ->subject('Reset Your Password - ' . $companyTitle)
            ->view('emails.password-reset')
            ->with([
                'user' => (object) ['name' => $name],
                'token' => $this->token,
                'resetUrl' => $resetUrl,
                'expiryMinutes' => $expires,
                'company_title' => $companyTitle,
                'company_email' => $companyEmail,
            ]);

        if ($companyEmail !== '') {
            $mail->from($companyEmail, $fromName !== '' ? $fromName : $companyTitle);
        }

        return $mail;
    }

    /**
     * Never use APP_URL here — that is the Laravel API. Prefer DB setting, then FRONTEND_URL.
     */
    private function resolveStorefrontBaseUrl(array $settings): string
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

        // Typical local Next.js dev server (override with FRONTEND_URL in .env for production).
        $fallback = 'http://127.0.0.1:3000';
        Log::warning('CustomerPasswordResetMail: FRONTEND_URL / frontend_url not set; using fallback', [
            'fallback' => $fallback,
        ]);

        return rtrim($fallback, '/');
    }
}
