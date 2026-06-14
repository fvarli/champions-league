<?php

namespace App\Support;

/**
 * Single source of truth for the public API version. The value is used by the
 * route prefix, the `X-API-Version` response header, and the docs so routing,
 * headers, and documentation can never drift apart. A future breaking change is
 * introduced as `v2` here (and as a parallel route group) without disturbing
 * existing clients.
 */
final class ApiVersion
{
    public const CURRENT = 'v1';
}
