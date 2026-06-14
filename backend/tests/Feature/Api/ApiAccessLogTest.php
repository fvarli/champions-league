<?php

namespace Tests\Feature\Api;

use App\Models\ApiAccessLog;
use App\Models\Fixture;
use App\Services\ApiAccessLogService;
use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ApiAccessLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The only columns this table is ever allowed to hold — no body/payload column.
     *
     * @var list<string>
     */
    private const EXPECTED_COLUMNS = [
        'id', 'request_id', 'method', 'path', 'route_name',
        'status_code', 'duration_ms', 'ip', 'user_agent', 'created_at',
    ];

    public function test_an_api_request_creates_an_access_log_row(): void
    {
        $this->seed(TeamSeeder::class);
        $this->assertSame(0, ApiAccessLog::query()->count());

        $this->getJson('/api/v1/teams')->assertOk();

        $this->assertSame(1, ApiAccessLog::query()->count());
    }

    public function test_the_log_request_id_matches_the_response_header_and_body(): void
    {
        $this->seed(TeamSeeder::class);

        $response = $this->getJson('/api/v1/teams')->assertOk();
        $log = ApiAccessLog::query()->latest('id')->firstOrFail();

        $this->assertNotEmpty($log->request_id);
        $this->assertSame($response->headers->get('X-Request-Id'), $log->request_id);
        $this->assertSame($response->json('request_id'), $log->request_id);
    }

    public function test_a_supplied_request_id_is_correlated(): void
    {
        $this->seed(TeamSeeder::class);

        $this->getJson('/api/v1/teams', ['X-Request-Id' => 'corr-xyz'])->assertOk();

        $this->assertSame('corr-xyz', ApiAccessLog::query()->latest('id')->firstOrFail()->request_id);
    }

    public function test_the_log_stores_core_request_metadata(): void
    {
        $this->seed(TeamSeeder::class);

        $this->getJson('/api/v1/teams', ['User-Agent' => 'PHPUnit-Agent'])->assertOk();
        $log = ApiAccessLog::query()->latest('id')->firstOrFail();

        $this->assertSame('GET', $log->method);
        $this->assertSame('api/v1/teams', $log->path);
        $this->assertSame(200, $log->status_code);
        $this->assertGreaterThanOrEqual(0.0, (float) $log->duration_ms);
        $this->assertNotNull($log->ip);
        $this->assertSame('PHPUnit-Agent', $log->user_agent);
        $this->assertNotNull($log->created_at);
    }

    public function test_no_request_body_is_stored(): void
    {
        $this->seed(TeamSeeder::class);

        // Structural guarantee: there is no column that could hold a payload.
        $this->assertFalse(Schema::hasColumn('api_access_logs', 'body'));
        $this->assertFalse(Schema::hasColumn('api_access_logs', 'request_body'));
        $this->assertFalse(Schema::hasColumn('api_access_logs', 'payload'));
        $this->assertFalse(Schema::hasColumn('api_access_logs', 'request'));

        // A request that carries a body leaves only metadata behind.
        $this->postJson('/api/v1/fixtures/generate')->assertSuccessful();
        $fixtureId = Fixture::query()->value('id');

        $this->patchJson("/api/v1/fixtures/{$fixtureId}/score", ['home_score' => 4, 'away_score' => 2])->assertOk();
        $log = ApiAccessLog::query()->where('path', 'like', '%/score')->latest('id')->firstOrFail();

        $this->assertEqualsCanonicalizing(self::EXPECTED_COLUMNS, array_keys($log->getAttributes()));
    }

    public function test_no_response_body_is_stored(): void
    {
        $this->seed(TeamSeeder::class);

        // The teams response body contains team names; none must leak into the log.
        $response = $this->getJson('/api/v1/teams')->assertOk();
        $teamName = $response->json('data.0.name');
        $this->assertNotEmpty($teamName);

        $log = ApiAccessLog::query()->latest('id')->firstOrFail();
        $this->assertEqualsCanonicalizing(self::EXPECTED_COLUMNS, array_keys($log->getAttributes()));

        foreach ($log->getAttributes() as $value) {
            $this->assertStringNotContainsStringIgnoringCase((string) $teamName, (string) $value);
        }
    }

    public function test_error_responses_are_logged_too(): void
    {
        $this->seed(TeamSeeder::class);

        // A matched route with a missing model binding produces a JSON 404.
        $this->patchJson('/api/v1/fixtures/999999/score', ['home_score' => 1, 'away_score' => 0])
            ->assertNotFound();

        $log = ApiAccessLog::query()->latest('id')->firstOrFail();
        $this->assertSame(404, $log->status_code);
        $this->assertSame('PATCH', $log->method);
        $this->assertSame('api/v1/fixtures/999999/score', $log->path);
    }

    public function test_a_logging_failure_does_not_break_the_api_response(): void
    {
        $this->seed(TeamSeeder::class);

        $throwing = $this->createMock(ApiAccessLogService::class);
        $throwing->method('log')->willThrowException(new RuntimeException('logging is down'));
        $this->app->instance(ApiAccessLogService::class, $throwing);

        $this->getJson('/api/v1/teams')
            ->assertOk()
            ->assertJsonStructure(['data', 'request_id']);
    }
}
