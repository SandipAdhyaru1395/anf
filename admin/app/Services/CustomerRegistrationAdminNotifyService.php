<?php

namespace App\Services;

use App\Mail\CustomerRegisteredAdminMail;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerRegistrationAdminNotifyService
{
    /**
     * Notify shop admin(s) that a new customer registered online (pending approval).
     *
     * Recipients from settings only (first match wins): registration_notify_email
     * (comma-separated), then company_email, then mail_from_address.
     */
    public function send(Customer $customer): void
    {
        $settings = Setting::query()->pluck('value', 'key')->toArray();
        $recipients = $this->resolveRecipients($settings);

        if ($recipients === []) {
            Log::info('Customer registration admin notify skipped: no admin email configured', [
                'customer_id' => $customer->id,
            ]);
            return;
        }

        try {
            Mail::to($recipients)->queue(new CustomerRegisteredAdminMail($customer));
        } catch (\Throwable $e) {
            Log::warning('Failed to queue customer registration admin email', [
                'customer_id' => $customer->id,
                'recipients' => $recipients,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return list<string>
     */
    private function resolveRecipients(array $settings): array
    {
        $list = trim((string) ($settings['registration_notify_email'] ?? ''));
        if ($list !== '') {
            $emails = array_values(array_filter(array_map('trim', explode(',', $list))));
            if ($emails !== []) {
                return $emails;
            }
        }

        foreach (['company_email', 'mail_from_address'] as $key) {
            $e = trim((string) ($settings[$key] ?? ''));
            if ($e !== '') {
                return [$e];
            }
        }

        return [];
    }
}
