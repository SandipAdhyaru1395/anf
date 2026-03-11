<?php

namespace App\Services\Planufac;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PlanufacClient
{
    private const CACHE_TOKEN_KEY = 'planufac.api.token';

    private ?string $baseUrl;
    private ?string $email;
    private ?string $password;
    private int $timeoutSeconds;

    public function __construct(?string $baseUrl = null, ?string $email = null, ?string $password = null, int $timeoutSeconds = 20)
    {
        $cfg = (array) config('services.planufac');

        $this->baseUrl = $baseUrl ?? ($cfg['base_url'] ?? null);
        $this->email = $email ?? ($cfg['email'] ?? null);
        $this->password = $password ?? ($cfg['password'] ?? null);
        $this->timeoutSeconds = (int) ($cfg['timeout'] ?? $timeoutSeconds);
    }

    private function http(?string $token = null): PendingRequest
    {
        $req = Http::baseUrl(rtrim((string) $this->baseUrl, '/'))
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeoutSeconds);

        if ($token) {
            $req = $req->withToken($token);
        }

        return $req;
    }

    public function getAccessToken(): string
    {
        $cached = Cache::get(self::CACHE_TOKEN_KEY);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        return $this->login();
    }

    /**
     * Login and cache token.
     *
     * @throws RequestException
     */
    public function login(): string
    {
        if (!$this->baseUrl || !$this->email || !$this->password) {
            throw new \RuntimeException('Planufac ERP credentials not configured. Set PLANUFAC_BASE_URL, PLANUFAC_EMAIL, PLANUFAC_PASSWORD.');
        }

        $resp = $this->http()->post('/api/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
        ])->throw();

        $json = $resp->json();

        // Common shapes: {access_token, expires_in} OR {token, expires_in}
        $token = $json['access_token'] ?? $json['token'] ?? null;
        if (!is_string($token) || $token === '') {
            throw new \RuntimeException('Planufac login did not return an access token.');
        }

        $expiresIn = (int) ($json['expires_in'] ?? $json['expires'] ?? 3600);
        $ttl = max(60, $expiresIn - 60); // keep 60s buffer

        Cache::put(self::CACHE_TOKEN_KEY, $token, $ttl);

        return $token;
    }

    /**
     * Refresh token (if supported by API) and cache.
     */
    public function refresh(): ?string
    {
        $token = Cache::get(self::CACHE_TOKEN_KEY);
        if (!is_string($token) || $token === '') {
            return null;
        }

        try {
            $resp = $this->http($token)->post('/api/auth/refresh')->throw();
        } catch (\Throwable) {
            return null;
        }

        $json = $resp->json();
        $newToken = $json['access_token'] ?? $json['token'] ?? null;
        if (!is_string($newToken) || $newToken === '') {
            return null;
        }

        $expiresIn = (int) ($json['expires_in'] ?? $json['expires'] ?? 3600);
        $ttl = max(60, $expiresIn - 60);
        Cache::put(self::CACHE_TOKEN_KEY, $newToken, $ttl);

        return $newToken;
    }

    /**
     * GET /api/products with pagination.
     *
     * Returns ['items' => array, 'total' => int|null]
     */
    public function listProducts(int $length = 200, int $start = 0, string $orderBy = 'products.name', string $direction = 'asc', string $q = ''): array
    {
        $token = $this->getAccessToken();

        try {
            $resp = $this->http($token)
                ->retry(2, 250)
                ->get('/api/products', [
                    'length' => $length,
                    'start' => $start,
                    'orderBy' => $orderBy,
                    'direction' => $direction,
                    'q' => $q,
                ])
                ->throw();
        } catch (RequestException $e) {
            // If token expired, attempt refresh once.
            if ($e->response && $e->response->status() === 401) {
                $newToken = $this->refresh() ?? $this->login();
                $resp = $this->http($newToken)
                    ->retry(2, 250)
                    ->get('/api/products', [
                        'length' => $length,
                        'start' => $start,
                        'orderBy' => $orderBy,
                        'direction' => $direction,
                        'q' => $q,
                    ])
                    ->throw();
            } else {
                throw $e;
            }
        }

        $json = $resp->json();

        $items = [];
        if (is_array($json)) {
            if (isset($json['data']) && is_array($json['data'])) {
                $items = $json['data'];
            } elseif (isset($json['products']) && is_array($json['products'])) {
                $items = $json['products'];
            } elseif (array_is_list($json)) {
                $items = $json;
            }
        }

        $total = null;
        if (is_array($json)) {
            $total = $json['recordsTotal'] ?? $json['total'] ?? ($json['meta']['total'] ?? null);
            if ($total !== null) {
                $total = (int) $total;
            }
        }

        return ['items' => $items, 'total' => $total];
    }
}

