<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class StoreMaintenanceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $val = Setting::where('key', 'maintenance_mode')->value('value');
            $on = $val === '1';
        } catch (\Throwable $e) {
            $on = false;
        }

        if ($on) {
            return response()->json([
                'success' => false,
                'message' => 'Store is under maintenance',
            ], 503);
        }

        return $next($request);
    }
}


