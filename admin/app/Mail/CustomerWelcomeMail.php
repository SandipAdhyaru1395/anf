<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Setting;
use App\Support\MailSettingsHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Customer $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function build()
    {
        $settings = Setting::query()->pluck('value', 'key')->toArray();
        $companyTitle = (string) ($settings['company_title'] ?? config('app.name'));
        $companyEmail = MailSettingsHelper::fromAddress($settings);
        $fromName = MailSettingsHelper::fromName($settings);
        $discountPercent = (string) ($settings['welcome_discount_percent'] ?? '10');
        $welcomeCode = (string) ($settings['welcome_discount_code'] ?? 'WELCOME10');

        $mail = $this
            ->subject('Welcome to ' . $companyTitle)
            ->view('emails.customer-welcome')
            ->with([
                'user' => (object) ['name' => ($this->customer->company_name ?? 'Customer')],
                'discountPercent' => $discountPercent,
                'welcomeCode' => $welcomeCode,
                'company_title' => $companyTitle,
                'company_email' => $companyEmail,
            ]);

        if ($companyEmail !== '') {
            $mail->from($companyEmail, $fromName !== '' ? $fromName : $companyTitle);
        }

        return $mail;
    }
}
