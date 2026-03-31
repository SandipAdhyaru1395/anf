<?php

namespace App\Services;

use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderConfirmationEmailService
{
    public function sendForOrder(Order $order): void
    {
        $order->loadMissing(['customer', 'items.product', 'shippingBranch']);

        $email = trim((string) optional($order->customer)->email);
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->queue(new OrderConfirmationMail($order));
        } catch (\Throwable $e) {
            Log::warning('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
