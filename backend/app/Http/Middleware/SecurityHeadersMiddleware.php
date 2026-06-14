<?php

namespace App\Http\Middleware;

use App\Support\ApiVersion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds a conservative set of security headers to API responses, plus the
 * `X-API-Version` header advertising the served API version. No strict
 * Content-Security-Policy is set, to avoid breaking local frontend/API usage.
 */
class SecurityHeadersMiddleware
{
    /**
     * @var array<string, string>
     */
    private const HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        'Cross-Origin-Resource-Policy' => 'same-origin',
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'X-API-Version' => ApiVersion::CURRENT,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/*')) {
            foreach (self::HEADERS as $header => $value) {
                $response->headers->set($header, $value);
            }
        }

        return $response;
    }
}
