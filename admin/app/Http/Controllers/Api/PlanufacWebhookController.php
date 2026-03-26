<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanufacWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // For now: log only what we receive, no processing.
        // Avoid logging sensitive auth headers by default.
        Log::info('Planufac webhook received', [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'path' => $request->path(),
            'content_type' => $request->header('content-type'),
            'user_agent' => $request->header('user-agent'),
            'payload' => $request->all(),
        ]);

        $response = [
            'ok' => true,
        ];

        Log::info('Planufac webhook response', $response);

        return response()->json($response);
    }
}

