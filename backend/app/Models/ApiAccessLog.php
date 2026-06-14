<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A lightweight, observability-only record of a single API request: enough to
 * correlate an `X-Request-Id` with the route, status, and timing that produced
 * it. No request or response payloads are ever stored here.
 */
class ApiAccessLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_access_logs';

    /**
     * Only `created_at` is tracked; these rows are immutable once written.
     *
     * @var string|null
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'duration_ms' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }
}
