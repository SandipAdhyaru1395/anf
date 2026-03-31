<?php

namespace App\Services;

use App\Mail\OrderCancelledMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderStatusEmailService
{
    public function sendCancelled(Order $order): void
    {
        $order->loadMissing(['customer']);

        $email = trim((string) optional($order->customer)->email);
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->queue(new OrderCancelledMail($order));
        } catch (\Throwable $e) {
            Log::warning('Failed to queue cancelled order email', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
