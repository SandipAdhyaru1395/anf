<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use App\Support\MailSettingsHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $order = $this->order;
        $customer = $order->customer;
        $settings = Setting::query()->pluck('value', 'key')->toArray();

        $displayName = (string) ($customer->company_name ?? '');
        if ($displayName === '') {
            $displayName = 'Customer';
        }

        $companyTitle = (string) ($settings['company_title'] ?? config('app.name'));
        $companyEmail = MailSettingsHelper::fromAddress($settings);
        $fromName = MailSettingsHelper::fromName($settings);

        $mail = $this
            ->subject('Order Update - #' . $order->order_number . ' | ' . $companyTitle)
            ->view('emails.order-cancelled')
            ->with([
                'order' => $order,
                'user' => (object) ['name' => $displayName],
                'status' => 'cancelled',
                'statusLabel' => 'Cancelled',
                'statusMessage' => 'Your order has been cancelled by our team. If this was unexpected, please contact us and we will help immediately.',
                'company_title' => $companyTitle,
                'company_email' => $companyEmail,
            ]);

        if ($companyEmail !== '') {
            $mail->from($companyEmail, $fromName !== '' ? $fromName : $companyTitle);
        }

        return $mail;
    }
}
