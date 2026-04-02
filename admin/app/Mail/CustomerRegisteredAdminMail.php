<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Setting;
use App\Support\MailSettingsHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerRegisteredAdminMail extends Mailable implements ShouldQueue
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

        $reviewUrl = route('customer.overview', ['id' => $this->customer->id], true);

        $mail = $this
            ->subject('New customer registration (pending approval) - ' . $companyTitle)
            ->view('emails.customer-registered-admin')
            ->with([
                'customer' => $this->customer,
                'reviewUrl' => $reviewUrl,
                'company_title' => $companyTitle,
                'company_email' => $companyEmail,
            ]);

        if ($companyEmail !== '') {
            $mail->from($companyEmail, $fromName !== '' ? $fromName : $companyTitle);
        }

        $custEmail = trim((string) ($this->customer->email ?? ''));
        if ($custEmail !== '') {
            $replyName = trim((string) ($this->customer->contact_person_name ?? ''));
            if ($replyName === '') {
                $replyName = trim((string) ($this->customer->company_name ?? ''));
            }
            $mail->replyTo($custEmail, $replyName !== '' ? $replyName : $custEmail);
        }

        return $mail;
    }
}
