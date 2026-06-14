<?php

namespace App\Http\Middleware;

use App\Services\ApiAccessLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Times each API request and hands it to the access-log service after the
 * response is produced. The original response is returned untouched, and any
 * logging error is swallowed so observability can never break the API.
 */
class ApiAccessLogMiddleware
{
    public function __construct(private readonly ApiAccessLogService $logger) {}

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        $response = $next($request);

        $durationMs = (microtime(true) - $startedAt) * 1000;

        try {
            $this->logger->log($request, $response, $durationMs);
        } catch (Throwable $e) {
            report($e);
        }

        return $response;
    }
}
