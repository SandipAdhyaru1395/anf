<?php

namespace App\Services;

use DNAPayments\DNAPayments;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class DnaPaymentsService
{
    /**
     * @return array{enabled:bool,mode:string,clientId:string,clientSecret:string,terminalId:string,returnUrl:string,failureUrl:string,callbackUrl:string,apiUrl:string,authUrl:string}
     */
    private function getConfig(): array
    {
        $settings = Setting::all()->pluck('value', 'key');
        $mode = $settings['dna_payments_mode'] ?? env('DNA_PAYMENTS_MODE', 'test');
        $isTest = ($mode ?: 'test') === 'test';
        $apiUrl = $isTest ? 'https://test-api.dnapayments.com' : 'https://api.dnapayments.com';
        $authUrl = $isTest ? 'https://test-oauth.dnapayments.com/oauth2/token' : 'https://oauth.dnapayments.com/oauth2/token';

        $secretEnc = $settings['dna_payments_client_secret'] ?? null;
        $secret = null;
        if (is_string($secretEnc) && $secretEnc !== '') {
            try {
                $secret = Crypt::decryptString($secretEnc);
            } catch (\Throwable) {
                $secret = null;
            }
        }

        return [
            'enabled' => ($settings['dna_payments_enabled'] ?? '0') === '1',
            'mode' => $mode,
            'clientId' => $settings['dna_payments_client_id'] ?? env('DNA_PAYMENTS_CLIENT_ID'),
            'clientSecret' => $secret ?? env('DNA_PAYMENTS_CLIENT_SECRET'),
            'terminalId' => $settings['dna_payments_terminal_id'] ?? env('DNA_PAYMENTS_TERMINAL_ID'),
            'returnUrl' => env('DNA_PAYMENTS_RETURN_URL'),
            'failureUrl' => env('DNA_PAYMENTS_FAILURE_URL'),
            'callbackUrl' => env('DNA_PAYMENTS_CALLBACK_URL'),
            'apiUrl' => $apiUrl,
            'authUrl' => $authUrl,
        ];
    }

    /**
     * Get OAuth2 access token for DNA Open Banking (Ecospend).
     *
     * DNA Open Banking auth is transaction-specific and requires scope "webapi payment"
     * plus invoiceId/amount/currency/terminal.
     */
    private function getOpenBankingAccessToken(string $invoiceId, float $amount, string $currency, string $terminalId): string
    {
        $config = $this->getConfig();
        if (!$config['enabled'] || empty($config['clientId']) || empty($config['clientSecret']) || empty($terminalId)) {
            throw new \RuntimeException('Payment Gateway is not configured.');
        }
        $response = Http::asForm()->post($config['authUrl'], [
            'grant_type' => 'client_credentials',
            'scope' => 'webapi payment',
            'client_id' => $config['clientId'],
            'client_secret' => $config['clientSecret'],
            'invoiceId' => $invoiceId,
            'amount' => round($amount, 2),
            'currency' => $currency,
            'terminal' => $terminalId,
        ]);
        if (!$response->successful()) {
            throw new \RuntimeException('Payment Gateway authentication failed.');
        }
        $data = $response->json();
        if (empty($data['access_token'])) {
            throw new \RuntimeException('Payment Gateway authentication failed.');
        }
        return $data['access_token'];
    }

    /**
     * Get list of banks available for Pay by Bank (Open Banking).
     *
     * @return array<int, array{bank_id:string,name:string,friendly_name:string,logo?:string,icon?:string}>
     */
    public function getBanks(): array
    {
        $config = $this->getConfig();
        if (!$config['enabled']) {
            throw new \RuntimeException('Payment Gateway is disabled.');
        }
        if (empty($config['clientId']) || empty($config['clientSecret']) || empty($config['terminalId'])) {
            throw new \RuntimeException('Payment Gateway is not configured.');
        }
        // Open Banking requires transaction-scoped auth; use a short-lived, synthetic token for listing banks.
        $token = $this->getOpenBankingAccessToken((string) \Illuminate\Support\Str::uuid(), 1.00, 'GBP', $config['terminalId']);
        $response = Http::withToken($token)
            ->acceptJson()
            ->get($config['apiUrl'] . '/v1/ecospend/banks');
        if (!$response->successful()) {
            throw new \RuntimeException('Unable to load bank list.');
        }
        $body = $response->json();
        $data = $body['data'] ?? [];
        return is_array($data) ? $data : [];
    }

    /**
     * Create a Pay by Bank (Open Banking) payment and return redirect URL.
     *
     * @param string $invoiceId
     * @param float $amount
     * @param string $currency
     * @param string $bankId Bank ID from getBanks()
     * @param array<string,mixed> $metadata
     * @param array{firstName:string,lastName?:string,streetAddress1:string,postalCode?:string,city:string,country:string} $billingAddress
     * @return array{checkoutUrl:string,paymentId:string}
     */
    public function createBankPayment(string $invoiceId, float $amount, string $currency, string $bankId, array $metadata = [], array $billingAddress = []): array
    {
        $config = $this->getConfig();
        if (!$config['enabled']) {
            throw new \RuntimeException('Payment Gateway is disabled.');
        }
        if (empty($config['clientId']) || empty($config['clientSecret']) || empty($config['terminalId'])) {
            throw new \RuntimeException('Payment Gateway is not configured.');
        }
        if (empty($config['returnUrl']) || empty($config['callbackUrl'])) {
            throw new \RuntimeException('Payment Gateway return/callback URLs are not configured.');
        }
        $token = $this->getOpenBankingAccessToken($invoiceId, $amount, $currency, $config['terminalId']);

        $payload = [
            'bankId' => $bankId,
            'amount' => round($amount, 2),
            'currency' => $currency,
            'invoiceId' => $invoiceId,
            'description' => 'Online order',
            'terminalId' => $config['terminalId'],
            'paymentMethod' => 'ecospend',
            'transactionType' => 'SALE',
            'returnUrl' => $config['returnUrl'],
            'callbackUrl' => $config['callbackUrl'],
            'failureCallbackUrl' => $config['failureUrl'] ?: $config['returnUrl'],
            'billingAddress' => [
                'firstName' => $billingAddress['firstName'] ?? 'Customer',
                'lastName' => $billingAddress['lastName'] ?? '',
                'streetAddress1' => $billingAddress['streetAddress1'] ?? 'N/A',
                'postalCode' => $billingAddress['postalCode'] ?? '',
                'city' => $billingAddress['city'],
                'country' => $billingAddress['country'],
            ],
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($config['apiUrl'] . '/payments/alternative/createOrder', $payload);

        if (!$response->successful()) {
            $msg = $response->json('message') ?? $response->body();
            throw new \RuntimeException($msg ?: 'Pay by bank request failed.');
        }
        $data = $response->json();
        $bankUrl = $data['bankUrl'] ?? null;
        $id = $data['id'] ?? $invoiceId;
        if (empty($bankUrl)) {
            throw new \RuntimeException('Pay by bank redirect URL not received.');
        }
        return [
            'checkoutUrl' => $bankUrl,
            'paymentId' => $id,
        ];
    }

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
        $config = $this->getConfig();
        if (!$config['enabled']) {
            throw new \RuntimeException('Payment Gateway is disabled.');
        }
        if (empty($config['clientId']) || empty($config['clientSecret']) || empty($config['terminalId'])) {
            throw new \RuntimeException('Payment Gateway is not configured.');
        }
        $clientId = $config['clientId'];
        $clientSecret = $config['clientSecret'];
        $terminalId = $config['terminalId'];
        $returnUrl = $config['returnUrl'];
        $failureUrl = $config['failureUrl'];
        $callbackUrl = $config['callbackUrl'];
        $mode = $config['mode'];

        // Configure global settings on the SDK (hosted checkout)
        DNAPayments::configure([
            'isTestMode' => ($mode ?: 'test') === 'test',
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

