<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
            if ($src !== null) {
                return [
                    'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' : (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : '')
                ];
            }
            return [];
        });


        $setting_logo = Setting::where('key', 'company_logo')->first()->value;

        $setting_title = Setting::where('key', 'company_title')->first()->value;

        $setting_currency_symbol = Setting::where('key', 'currency_symbol')->first()->value;


        view()->share('setting', [
            'company_logo' => $setting_logo,
            'company_title' => $setting_title,
            'currency_symbol' => $setting_currency_symbol,
        ]);

        $sessionTimeout = Setting::where('key', 'session_timeout')->first()?->value;
       
        config([
            'variables.templateName' => $setting_title,
            'variables.ogTitle' => $setting_title,
            'session.lifetime' => ($sessionTimeout && $sessionTimeout > 0) ? $sessionTimeout : 120
        ]);
    }
}
