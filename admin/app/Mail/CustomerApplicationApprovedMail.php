<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Setting;
use App\Support\MailSettingsHelper;
use App\Support\StorefrontUrlHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerApplicationApprovedMail extends Mailable implements ShouldQueue
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

        $customerName = trim((string) ($this->customer->contact_person_name ?? ''));
        if ($customerName === '') {
            $customerName = trim((string) ($this->customer->company_name ?? ''));
        }
        if ($customerName === '') {
            $customerName = 'Customer';
        }

        $storefrontUrl = StorefrontUrlHelper::baseUrl($settings);

        $mail = $this
            ->subject('Your application has been approved - ' . $companyTitle)
            ->view('emails.customer-application-approved')
            ->with([
                'customerName' => $customerName,
                'storefrontUrl' => $storefrontUrl,
                'company_title' => $companyTitle,
                'company_email' => $companyEmail,
            ]);

        if ($companyEmail !== '') {
            $mail->from($companyEmail, $fromName !== '' ? $fromName : $companyTitle);
        }

        return $mail;
    }
}
