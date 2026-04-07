<?php

namespace App\Mail;

use App\Helpers\Helpers;
use App\Models\Order;
use App\Models\Setting;
use App\Support\MailSettingsHelper;
use App\Support\StorefrontUrlHelper;
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
        $storefrontBase = StorefrontUrlHelper::baseUrl($settings);
        $viewOrderUrl = $storefrontBase . '/?order=' . rawurlencode((string) $order->order_number);

        $mail = $this
            ->subject('Order Confirmed - #' . $order->order_number)
            ->view('emails.order-confirmation')
            ->with([
                'order' => $order,
                'user' => (object) ['name' => $displayName],
                'payment_summary' => $paymentSummary,
                'shipping_address' => $shippingBranch,
                'estimated_delivery' => $order->estimated_delivery_date
                    ? Helpers::displayDateTime($order->estimated_delivery_date)
                    : '',
                'company_title' => $companyTitle,
                'company_email' => $companyEmail,
                'view_order_url' => $viewOrderUrl,
            ]);

        if ($companyEmail !== '') {
            $mail->from($companyEmail, $fromName !== '' ? $fromName : $companyTitle);
        }

        return $mail;
    }
}
