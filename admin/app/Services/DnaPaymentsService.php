<?php

namespace App\Services;

use DNAPayments\DNAPayments;
use App\Models\Setting;

class DnaPaymentsService
{

    /**
     * Create a hosted checkout payment for the given order draft.
     *
     * @param string $invoiceId Unique invoice/order identifier
     * @param float $amount Total amount to charge
     * @param string $currency ISO currency code (e.g. GBP)
     * @param array<string,mixed> $metadata Extra data to associate with payment
     * @return array{checkoutUrl:string,paymentId:string}
     */
    public function createHostedPayment(string $invoiceId, float $amount, string $currency, array $metadata = []): array
    {
        // Load DNA config directly from settings table, env as last-resort fallback
        $settings = Setting::all()->pluck('value', 'key');

        $enabled      = ($settings['dna_payments_enabled'] ?? '0') === '1';
        $mode         = $settings['dna_payments_mode']        ?? env('DNA_PAYMENTS_MODE', 'test');
        $clientId     = $settings['dna_payments_client_id']   ?? env('DNA_PAYMENTS_CLIENT_ID');
        $clientSecret = $settings['dna_payments_client_secret'] ?? env('DNA_PAYMENTS_CLIENT_SECRET');
        $terminalId   = $settings['dna_payments_terminal_id'] ?? env('DNA_PAYMENTS_TERMINAL_ID');
        $returnUrl    = env('DNA_PAYMENTS_RETURN_URL');
        $failureUrl   = env('DNA_PAYMENTS_FAILURE_URL');
        $callbackUrl  = env('DNA_PAYMENTS_CALLBACK_URL');

        if (!$enabled) {
            throw new \RuntimeException('Payment Gateway is disabled.');
        }

        if (empty($clientId) || empty($clientSecret) || empty($terminalId)) {
            throw new \RuntimeException('Payment Gateway is not configured.');
        }

        // Configure global settings on the SDK (hosted checkout)
        DNAPayments::configure([
            'isTestMode' => ($mode ?? 'test') === 'test',
            'scopes' => [
                'allowHosted' => true,
            ],
        ]);

        // Step 1: get auth token for this invoice / amount
        $auth = DNAPayments::auth([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'terminal'      => $terminalId,
            'invoiceId'     => $invoiceId,
            'amount'        => $amount,
            'currency'      => $currency,
        ]);

        // Step 2: build payment data and generate hosted checkout URL
        $paymentData = [
            'invoiceId' => $invoiceId,
            'description' => 'Online order',
            'amount' => $amount,
            'currency' => $currency,
            'language' => 'en-gb',
            'paymentSettings' => [
                'terminalId'        => $terminalId,
                'returnUrl'         => $returnUrl,
                'failureReturnUrl'  => $failureUrl,
                'callbackUrl'       => $callbackUrl,
            ],
            'customData' => $metadata,
        ];

        $url = DNAPayments::generateUrl($paymentData, $auth);

        return [
            'checkoutUrl' => $url,
            'paymentId'   => $invoiceId,
        ];
    }
}

