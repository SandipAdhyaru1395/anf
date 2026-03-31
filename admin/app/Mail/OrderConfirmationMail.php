<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use App\Support\MailSettingsHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable implements ShouldQueue
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
        $shippingBranch = $order->shippingBranch;
        $settings = Setting::query()->pluck('value', 'key')->toArray();

        $displayName = (string) ($customer->company_name ?? '');
        if ($displayName === '') {
            $displayName = 'Customer';
        }

        $paymentSummary = (string) ($order->payment_status ?? '');
        $companyTitle = (string) ($settings['company_title'] ?? config('app.name'));
        $companyEmail = MailSettingsHelper::fromAddress($settings);
        $fromName = MailSettingsHelper::fromName($settings);

        $mail = $this
            ->subject('Order Confirmed - #' . $order->order_number)
            ->view('emails.order-confirmation')
            ->with([
                'order' => $order,
                'user' => (object) ['name' => $displayName],
                'payment_summary' => $paymentSummary,
                'shipping_address' => $shippingBranch,
                'estimated_delivery' => optional($order->estimated_delivery_date)->format('d M Y'),
                'company_title' => $companyTitle,
                'company_email' => $companyEmail,
            ]);

        if ($companyEmail !== '') {
            $mail->from($companyEmail, $fromName !== '' ? $fromName : $companyTitle);
        }

        return $mail;
    }
}
