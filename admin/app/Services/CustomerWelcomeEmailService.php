<?php

namespace App\Services;

use App\Mail\CustomerWelcomeMail;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerWelcomeEmailService
{
    public function send(Customer $customer): void
    {
        $email = trim((string) ($customer->email ?? ''));
        if ($email === '') {
            return;
        }

        try {
            Mail::to($email)->queue(new CustomerWelcomeMail($customer));
        } catch (\Throwable $e) {
            Log::warning('Failed to queue welcome email', [
                'customer_id' => $customer->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
