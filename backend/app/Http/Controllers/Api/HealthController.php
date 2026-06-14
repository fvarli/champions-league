<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * Liveness/readiness probe: confirms the app responds and the database
     * is reachable via a cheap query.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $requestId = $request->attributes->get('request_id');

        try {
            DB::select('select 1');
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'database' => 'unavailable',
                'message' => $e->getMessage(),
                'request_id' => $requestId,
            ], 503);
        }

        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'database' => 'ok',
            'timestamp' => now()->toISOString(),
            'request_id' => $requestId,
        ]);
    }
}
