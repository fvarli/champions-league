<?php

namespace App\Services;

use App\Models\ApiAccessLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Persists a lightweight access-log row for an API request/response pair. This
 * is observability, not analytics: only request metadata is stored — never the
 * request body, the response body, or any other payload.
 */
class ApiAccessLogService
{
    /**
     * Record a single API request. Logging must never break the API, so any
     * persistence failure is caught and reported rather than propagated.
     */
    public function log(Request $request, Response $response, float $durationMs): void
    {
        try {
            ApiAccessLog::create([
                'request_id' => $this->resolveRequestId($request, $response),
                'method' => Str::limit($request->getMethod(), 10, ''),
                'path' => Str::limit($request->path(), 500, ''),
                'route_name' => $this->resolveRouteName($request),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => round($durationMs, 2),
                'ip' => $request->ip(),
                'user_agent' => $this->resolveUserAgent($request),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Correlation id: prefer the value stamped onto the request by the
     * request-id middleware, then a supplied request header, then the response
     * header as a last resort.
     */
    private function resolveRequestId(Request $request, Response $response): ?string
    {
        $fromAttributes = $request->attributes->get('request_id');
        if (is_string($fromAttributes) && $fromAttributes !== '') {
            return $fromAttributes;
        }

        $fromRequestHeader = $request->header('X-Request-Id');
        if (is_string($fromRequestHeader) && $fromRequestHeader !== '') {
            return $fromRequestHeader;
        }

        $fromResponseHeader = $response->headers->get('X-Request-Id');

        return ($fromResponseHeader !== null && $fromResponseHeader !== '') ? $fromResponseHeader : null;
    }

    private function resolveRouteName(Request $request): ?string
    {
        $route = $request->route();

        return $route instanceof Route ? $route->getName() : null;
    }

    private function resolveUserAgent(Request $request): ?string
    {
        $userAgent = $request->userAgent();

        return $userAgent !== null ? Str::limit($userAgent, 500, '') : null;
    }
}
