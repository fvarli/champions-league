# Simulation Engine

Two pieces drive the league.

## Fixture generation

`FixtureGenerationService` builds a double round-robin schedule with the circle
method: four teams, six weeks, twelve fixtures, each pair meeting once at home
and once away. Generation is **deterministic** — the same teams always produce
the same schedule — and it refuses to run when fixtures already exist.

## Match simulation

`MatchSimulationService::simulate()` resolves a single unplayed fixture. Each
team's strength becomes a per-attempt scoring chance, the home side gets a small
**home-advantage** bonus, and goals are drawn from a bounded probabilistic
process (0–5 per team). Stronger teams score more often, while **random
variance** keeps draws and upsets possible.

Randomness is injected through PHP's `Random\Randomizer`, so tests can supply a
seeded engine for reproducible results.

## Orchestration

Actions in `app/Actions` drive the league forward without duplicating the
simulation logic:

- `PlayWeekAction` simulates every unplayed fixture in a given week.
- `PlayNextWeekAction` plays the earliest week that still has unplayed fixtures.
- `PlayAllRemainingFixturesAction` plays every remaining week in order and
  returns the played fixtures grouped by week.

Each fails clearly: an unknown week throws `InvalidWeekException`, a fully
played week throws `WeekAlreadyPlayedException`, and a finished league throws
`LeagueAlreadyCompleteException`.

## Protections

Already-played fixtures are protected: simulating a fixture that already has a
`played_at` timestamp throws `FixtureAlreadyPlayedException`, so results are
never overwritten.
