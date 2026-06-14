<?php

namespace Tests\Feature\Api;

use Database\Seeders\TeamSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_responses_include_a_request_id_header(): void
    {
        $this->seed(TeamSeeder::class);

        $response = $this->getJson('/api/v1/teams')->assertOk();

        $this->assertNotEmpty($response->headers->get('X-Request-Id'));
    }

    public function test_api_json_body_includes_request_id(): void
    {
        $this->seed(TeamSeeder::class);

        $this->getJson('/api/v1/teams')
            ->assertOk()
            ->assertJsonStructure(['data', 'request_id']);
    }

    public function test_a_provided_request_id_is_reused(): void
    {
        $this->seed(TeamSeeder::class);

        $response = $this->getJson('/api/v1/teams', ['X-Request-Id' => 'corr-123'])->assertOk();

        $this->assertSame('corr-123', $response->headers->get('X-Request-Id'));
        $response->assertJsonPath('request_id', 'corr-123');
    }

    public function test_api_errors_are_returned_as_json_not_html(): void
    {
        $response = $this->get('/api/v1/does-not-exist', ['Accept' => 'text/html']);

        $response->assertStatus(404);
        $this->assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'));
    }

    public function test_api_responses_include_security_headers(): void
    {
        $response = $this->getJson('/api/v1/health')->assertOk();

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
    }

    public function test_api_responses_advertise_the_api_version(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertHeader('X-API-Version', 'v1');
    }

    public function test_health_endpoint_reports_ok_with_request_id(): void
    {
        $response = $this->getJson('/api/v1/health')->assertOk();

        $response->assertJsonPath('status', 'ok')->assertJsonPath('database', 'ok');
        $this->assertNotEmpty($response->json('request_id'));
    }

    public function test_excessive_api_requests_are_rate_limited(): void
    {
        $status = 200;

        for ($i = 0; $i < 70 && $status !== 429; $i++) {
            $status = $this->getJson('/api/v1/health')->getStatusCode();
        }

        $this->assertSame(429, $status);
    }
}
