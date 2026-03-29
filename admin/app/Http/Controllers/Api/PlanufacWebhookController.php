<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncPlanufacInventoryLevelsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlanufacWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $requestId = (string) Str::uuid();

        $validated = $request->validate([
            'inventory_levels' => ['required', 'array'],
        ]);

        $levels = $validated['inventory_levels'];

        Log::info('Planufac webhook received', [
            'request_id' => $requestId,
            'ip' => $request->ip(),
            'method' => $request->method(),
            'inventory_levels_count' => count($levels),
            'payload' => $request->all(),
        ]);

        SyncPlanufacInventoryLevelsJob::dispatch($levels, $requestId);

        $response = [
            'ok' => true,
            'queued' => true,
            'request_id' => $requestId,
            'inventory_levels_received' => count($levels),
        ];

        Log::info('Planufac webhook accepted', $response);

        return response()->json($response, 202);
    }
}
