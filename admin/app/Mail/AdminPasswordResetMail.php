<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\User;
use App\Support\MailSettingsHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminPasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;

    public string $token;

    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function build()
    {
        $settings = Setting::query()->pluck('value', 'key')->toArray();
        $companyTitle = (string) ($settings['company_title'] ?? config('app.name'));
        $companyEmail = MailSettingsHelper::fromAddress($settings);
        $fromName = MailSettingsHelper::fromName($settings);

        $email = (string) ($this->user->email ?? '');
        $base = rtrim((string) config('app.url'), '/');
        $resetUrl = $base . '/auth/reset-password-cover/' . $this->token . '?' . http_build_query([
            'email' => $email,
        ]);

        $expires = (int) config('auth.passwords.users.expire', 60);

        $mail = $this
            ->subject('Reset your admin password — ' . $companyTitle)
            ->view('emails.password-reset')
            ->with([
                'user' => (object) ['name' => $this->user->name ?? 'User'],
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
}
