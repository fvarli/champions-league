<?php

namespace App\Services;

use App\Models\Fixture;
use App\Models\Team;
use Database\Seeders\TeamSeeder;
use Illuminate\Support\Facades\DB;

/**
 * Resets the demo league to its initial state: clears all fixtures while
 * preserving the seeded teams (re-seeding them only if they are missing).
 *
 * The operation runs in a transaction and is idempotent — running it repeatedly
 * leaves the same clean starting point. It never touches unrelated tables.
 */
class DemoResetService
{
    public function reset(): void
    {
        DB::transaction(function (): void {
            Fixture::query()->delete();

            if (Team::query()->doesntExist()) {
                (new TeamSeeder)->run();
            }
        });
    }
}
