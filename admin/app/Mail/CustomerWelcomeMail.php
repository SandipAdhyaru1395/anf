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
        $greetingName = trim((string) ($this->customer->contact_person_name ?? ''));
        if ($greetingName === '') {
            $greetingName = trim((string) ($this->customer->company_name ?? ''));
        }
        if ($greetingName === '') {
            $greetingName = 'Customer';
        }

        $mail = $this
            ->subject('Thank you for your application - ' . $companyTitle)
            ->view('emails.customer-welcome')
            ->with([
                'user' => (object) ['name' => $greetingName],
                'company_title' => $companyTitle,
                'company_email' => $companyEmail,
            ]);

        if ($companyEmail !== '') {
            $mail->from($companyEmail, $fromName !== '' ? $fromName : $companyTitle);
        }

        return $mail;
    }
}
