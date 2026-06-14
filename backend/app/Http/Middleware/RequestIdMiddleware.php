<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Assigns a correlation id to every request. An incoming X-Request-Id is reused;
 * otherwise a UUID is generated. The id is exposed on the response header and,
 * for JSON API responses, merged into the body from this single central place.
 */
class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-Id') ?: (string) Str::uuid();

        $request->attributes->set('request_id', $requestId);

        $response = $next($request);

        $response->headers->set('X-Request-Id', $requestId);

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            if (is_array($data) && ! array_is_list($data)) {
                $data['request_id'] = $requestId;
                $response->setData($data);
            }
        }

        return $response;
    }
}
