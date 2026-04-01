<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Planufac\PlanufacOrderWebhookPayloadBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPlanufacOrderWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public int $timeout = 60;

    public function __construct(
        public int $orderId,
    ) {
    }

    private const DEFAULT_SIGNATURE_HEADER = 'X-CustomOrderApp-Signature';

    /**
     * @return array{base_url: string, client_identifier: string, webhook_secret: ?string, webhook_header: string}
     */
    protected function loadWebhookConfigFromSettings(): array
    {
        $rows = Setting::query()
            ->whereIn('key', ['planufac_base_url', 'planufac_client_identifier', 'planufac_webhook_secret', 'planufac_webhook_header'])
            ->get(['key', 'value'])
            ->pluck('value', 'key')
            ->toArray();

        $secret = null;
        $enc = $rows['planufac_webhook_secret'] ?? null;
        if (is_string($enc) && $enc !== '') {
            try {
                $secret = Crypt::decryptString($enc);
            } catch (\Throwable) {
                $secret = null;
            }
        }

        $header = trim((string) ($rows['planufac_webhook_header'] ?? ''));
        if ($header === '' || ! preg_match('/^[A-Za-z0-9\-]+$/', $header)) {
            $header = self::DEFAULT_SIGNATURE_HEADER;
        }

        return [
            'base_url' => trim((string) ($rows['planufac_base_url'] ?? '')),
            'client_identifier' => trim((string) ($rows['planufac_client_identifier'] ?? '')),
            'webhook_secret' => is_string($secret) && $secret !== '' ? $secret : null,
            'webhook_header' => $header,
        ];
    }

    public function handle(): void
    {
        $cfg = $this->loadWebhookConfigFromSettings();

        if ($cfg['client_identifier'] === '' || $cfg['webhook_secret'] === null) {
            Log::warning('Planufac order webhook skipped: set client identifier and webhook secret in Settings → Planufac ERP', [
                'order_id' => $this->orderId,
            ]);

            return;
        }

        $order = Order::query()->find($this->orderId);
        if (! $order || strtoupper((string) $order->type) === 'EST') {
            return;
        }

        $builder = new PlanufacOrderWebhookPayloadBuilder;
        $payload = $builder->buildEnvelope($order);

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($body === false) {
            Log::error('Planufac order webhook: json_encode failed', ['order_id' => $this->orderId]);

            return;
        }

        $signature = base64_encode(hash_hmac('sha256', $body, $cfg['webhook_secret'], true));

        $baseRaw = $cfg['base_url'] !== '' ? $cfg['base_url'] : (string) config('services.planufac.base_url', 'https://sandbox.planufac.com');
        $base = rtrim($baseRaw, '/');
        $url = $base.'/webhooks/customorderapp/'.$cfg['client_identifier'];

        $signatureHeader = $cfg['webhook_header'];

        Log::info('Planufac order webhook request (payload + exact JSON body for HMAC)', [
            'order_id' => $this->orderId,
            'url' => $url,
            'signature_header' => $signatureHeader,
            'signature' => $signature,
            'payload' => $payload,
            'request_json' => $body,
        ]);

        try {
            $response = Http::timeout((int) config('services.planufac.timeout', 20))
                ->withHeaders(array_merge([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ], [
                    $signatureHeader => $signature,
                ]))
                ->withBody($body, 'application/json')
                ->post($url);

            $bodyPreview = $response->body();
            if (strlen($bodyPreview) > 2000) {
                $bodyPreview = substr($bodyPreview, 0, 2000).'…';
            }

            Log::info('Planufac order webhook response', [
                'order_id' => $this->orderId,
                'url' => $url,
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $bodyPreview,
            ]);

            if (! $response->successful()) {
                $response->throw();
            }
        } catch (\Throwable $e) {
            Log::error('Planufac order webhook failed', [
                'order_id' => $this->orderId,
                'url' => $url,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
