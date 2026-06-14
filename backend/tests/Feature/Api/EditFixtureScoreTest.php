<?php

namespace Tests\Feature\Api;

use App\Models\Fixture;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EditFixtureScoreTest extends TestCase
{
    use RefreshDatabase;

    private function team(string $name): Team
    {
        return Team::factory()->create(['name' => $name]);
    }

    private function fixture(Team $home, Team $away, ?int $homeScore = null, ?int $awayScore = null, ?Carbon $playedAt = null): Fixture
    {
        return Fixture::factory()->create([
            'home_team_id' => $home->id,
            'away_team_id' => $away->id,
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'played_at' => $playedAt,
        ]);
    }

    public function test_editing_an_unplayed_fixture_sets_scores_and_marks_it_played(): void
    {
        $fixture = $this->fixture($this->team('Liverpool'), $this->team('Arsenal'));

        $this->patchJson("/api/fixtures/{$fixture->id}/score", ['home_score' => 2, 'away_score' => 1])
            ->assertOk()
            ->assertJsonPath('message', 'Fixture score updated.')
            ->assertJsonPath('data.fixture.home_score', 2)
            ->assertJsonPath('data.fixture.away_score', 1)
            ->assertJsonPath('data.fixture.is_played', true);

        $fresh = $fixture->fresh();
        $this->assertSame(2, $fresh->home_score);
        $this->assertSame(1, $fresh->away_score);
        $this->assertNotNull($fresh->played_at);
    }

    public function test_editing_a_played_fixture_preserves_played_at(): void
    {
        $playedAt = now()->subDays(2)->startOfSecond();
        $fixture = $this->fixture($this->team('Liverpool'), $this->team('Arsenal'), 1, 1, $playedAt);

        $this->patchJson("/api/fixtures/{$fixture->id}/score", ['home_score' => 3, 'away_score' => 0])
            ->assertOk();

        $fresh = $fixture->fresh();
        $this->assertSame(3, $fresh->home_score);
        $this->assertSame(0, $fresh->away_score);
        $this->assertSame($playedAt->toDateTimeString(), $fresh->played_at->toDateTimeString());
    }

    public function test_editing_a_score_changes_standings(): void
    {
        $home = $this->team('Home');
        $away = $this->team('Away');
        $fixture = $this->fixture($home, $away, 1, 0, now());

        $response = $this->patchJson("/api/fixtures/{$fixture->id}/score", ['home_score' => 0, 'away_score' => 2])
            ->assertOk();

        $standings = collect($response->json('data.standings'));
        $this->assertSame(0, $standings->firstWhere('team.id', $home->id)['points']);
        $this->assertSame(3, $standings->firstWhere('team.id', $away->id)['points']);
    }

    public function test_editing_a_score_changes_predictions_when_the_league_is_complete(): void
    {
        $alpha = $this->team('Alpha');
        $bravo = $this->team('Bravo');
        $this->team('Charlie');
        $this->team('Delta');

        // The only fixture is played, so the league is "complete" — Alpha leads.
        $fixture = $this->fixture($alpha, $bravo, 3, 0, now());

        $response = $this->patchJson("/api/fixtures/{$fixture->id}/score", ['home_score' => 0, 'away_score' => 3])
            ->assertOk();

        $predictions = collect($response->json('data.predictions'));
        $this->assertNotEmpty($predictions);
        $this->assertSame($bravo->id, $predictions->firstWhere('percentage', 100)['team']['id']);
    }

    public function test_predictions_are_returned_after_editing_once_enough_fixtures_are_played(): void
    {
        $a = $this->team('Alfa');
        $b = $this->team('Bravo');
        $this->team('Cosmo');
        $this->team('Delta');

        $played = collect(range(1, 8))->map(fn (): Fixture => $this->fixture($a, $b, 1, 0, now()));
        $this->fixture($a, $b); // one unplayed fixture remains

        $response = $this->patchJson("/api/fixtures/{$played->first()->id}/score", ['home_score' => 2, 'away_score' => 2])
            ->assertOk();

        $predictions = $response->json('data.predictions');
        $this->assertCount(4, $predictions);
        $this->assertArrayNotHasKey('prediction_notice', $response->json('data'));
        $this->assertEqualsWithDelta(100, array_sum(array_column($predictions, 'percentage')), 1.0);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidScoreProvider(): array
    {
        return [
            'negative' => [-1],
            'above twenty' => [21],
            'null' => [null],
            'non-integer' => [2.5],
        ];
    }

    #[DataProvider('invalidScoreProvider')]
    public function test_invalid_scores_are_rejected(mixed $homeScore): void
    {
        $fixture = $this->fixture($this->team('Liverpool'), $this->team('Arsenal'));

        $this->patchJson("/api/fixtures/{$fixture->id}/score", ['home_score' => $homeScore, 'away_score' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors('home_score');

        $this->assertNull($fixture->fresh()->played_at);
    }

    public function test_editing_one_fixture_does_not_change_others(): void
    {
        $a = $this->team('A');
        $b = $this->team('B');
        $target = $this->fixture($a, $b, 1, 0, now());
        $other = $this->fixture($b, $a, 2, 2, now()->subDay()->startOfSecond());

        $this->patchJson("/api/fixtures/{$target->id}/score", ['home_score' => 4, 'away_score' => 1])
            ->assertOk();

        $freshOther = $other->fresh();
        $this->assertSame(2, $freshOther->home_score);
        $this->assertSame(2, $freshOther->away_score);
        $this->assertSame($other->played_at->toDateTimeString(), $freshOther->played_at->toDateTimeString());
    }

    public function test_response_includes_request_id(): void
    {
        $fixture = $this->fixture($this->team('Liverpool'), $this->team('Arsenal'), 1, 1, now());

        $this->patchJson("/api/fixtures/{$fixture->id}/score", ['home_score' => 2, 'away_score' => 0])
            ->assertOk()
            ->assertJsonStructure(['message', 'data' => ['fixture', 'standings', 'predictions'], 'request_id']);
    }
}
