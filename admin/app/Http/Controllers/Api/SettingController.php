<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\SyncUpdate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use function asset;

class SettingController extends Controller
{
    public function show()
    {
        $settings = Setting::all()->pluck('value', 'key');

        $logoPath = $settings->get('company_logo');
        $logoUrl = $logoPath ? asset('storage/'.$logoPath) : null;

        $bannerPath = $settings->get('banner');
        $bannerUrl = $bannerPath ? asset('storage/'.$bannerPath) : null;

        $thumbnailPath = $settings->get('company_thumbnail');
        $thumbnailUrl = $thumbnailPath ? asset('storage/'.$thumbnailPath) : null;

        // Determine if DNA payment gateway is available (enabled + fully configured)
        $dnaEnabled = ($settings->get('dna_payments_enabled') === '1');
        $dnaClientId = $settings->get('dna_payments_client_id');
        $dnaClientSecretEnc = $settings->get('dna_payments_client_secret');
        $dnaClientSecret = null;
        if (is_string($dnaClientSecretEnc) && $dnaClientSecretEnc !== '') {
            try {
                $dnaClientSecret = Crypt::decryptString($dnaClientSecretEnc);
            } catch (\Throwable) {
                $dnaClientSecret = null;
            }
        }
        $dnaTerminalId = $settings->get('dna_payments_terminal_id');
        $dnaGatewayAvailable = $dnaEnabled && !empty($dnaClientId) && !empty($dnaClientSecret) && !empty($dnaTerminalId);

        // Build versions map from sync_updates table, tolerating schema differences
        $versionsMap = [];
        try {
            $versionsMap = SyncUpdate::query()->pluck('version', 'entity')->toArray();
        } catch (\Throwable $e) {
            try {
                $versionsMap = DB::table('sync_updates')->pluck('version', 'model')->toArray();
            } catch (\Throwable $e2) {
                $versionsMap = [];
            }
        }

        return response()->json([
            'success' => true,
            'settings' => [
                'company_title' => $settings->get('company_title'),
                'company_logo_url' => $logoUrl,
                'currency_symbol' => $settings->get('currency_symbol') ?? '',
                'banner' => $bannerUrl,
                'thumbnail' => $thumbnailUrl,
                'maintenance_mode_store' => $settings->get('maintenance_mode_store') === '1',
                'payment_gateway_available' => $dnaGatewayAvailable,
                'theme' => [
                    'use_default' => $settings->get('default_theme') === '1',
                    'primary_bg_color' => $settings->get('primary_bg_color') ?? $settings->get('theme_primary_color'),
                    'primary_font_color' => $settings->get('primary_font_color') ?? $settings->get('theme_secondary_color'),
                    'secondary_bg_color' => $settings->get('secondary_bg_color'),
                    'secondary_font_color' => $settings->get('secondary_font_color'),
                    'button_login' => $settings->get('theme_button_login'),
                ],
            ],
            'versions' => [
                'Product' => (int)($versionsMap['Product'] ?? 0),
                'Order' => (int)($versionsMap['Order'] ?? 0),
                'Customer' => (int)($versionsMap['Customer'] ?? 0),
            ],
        ]);
    }

    public function deliveryMethods()
    {
        $methods = \App\Models\DeliveryMethod::where('status', 'active')
            ->orderByRaw('COALESCE(sort_order, 9999), name')
            ->get(['id', 'name', 'time', 'rate', 'status']);
        return response()->json([
            'success' => true,
            'delivery_methods' => $methods,
        ]);
    }
}


