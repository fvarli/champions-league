# Requirements Audit

A checklist mapping the assignment brief (and FAQ) to what is implemented, with
evidence. Status is one of **Done** (required item implemented), **Extra**
(optional "strong plus" implemented), or **Future** (not yet implemented).

## Required

| Requirement | Status | Evidence |
| --- | --- | --- |
| Four teams | Done | `backend/database/seeders/TeamSeeder.php` seeds exactly Liverpool (90), Manchester City (88), Chelsea (82), Arsenal (80); `TeamSeederTest`. |
| Strength-based match simulation (stronger usually wins, weaker still can) | Done | `MatchSimulationService::generateScore()` converts strength + home advantage into per-attempt scoring chance with injected randomness; `MatchSimulationServiceTest` asserts stronger teams win more often and a weaker team can still win. |
| Premier League scoring (3/1/0, GD, GF, points) | Done | `TeamStanding::points()` = `won*3 + drawn`; `LeagueStandingsService` sorts by points → goal difference → goals for → name; `LeagueStandingsServiceTest`. |
| Week-by-week simulation | Done | `PlayWeekAction` / `PlayNextWeekAction`; `POST /api/weeks/{week}/play`, `POST /api/weeks/next/play`; dashboard "Play Next Week" / "Play Week"; `PlayLeagueActionsTest`. |
| League table updates after each week | Done | `LeagueStandingsService::calculate()` derives standings from played fixtures; `GET /api/standings`; the store refreshes standings after every action and `StandingsTable.vue` re-renders. |
| Prediction after the 4th week / last weeks | Done | `ChampionshipPredictionService` (Monte Carlo) gated at `MIN_PLAYED_FIXTURES = 8` (after week 4) or league complete; `GET /api/predictions`; `ChampionshipPredictionServiceTest`; `PredictionPanel.vue` shows an "X / 8" unlock progress. See note below. |
| PHP / Laravel backend | Done | Laravel 12 application under `backend/`. |
| JavaScript front-end with a component design pattern (Vue) | Done | Vue 3 + TypeScript SPA; component-based UI under `frontend/src/components/` (AppShell, StandingsTable, FixtureWeekCard, PredictionPanel, ActionPanel, WeekPicker, …). |
| OOP | Done | Service/action layering and value objects: `app/Services/*`, `app/Actions/*`, immutable `TeamStanding` / `MatchResult` / `ChampionChance`. |
| Automated tests | Done | PHPUnit feature/unit tests under `backend/tests/` (73 tests) covering fixtures, simulation, standings, prediction, orchestration, and the API. |
| Shareable project link (Git host) | Done (repo) | Git repository with full history and README, hosted on GitHub. A live deployed URL is planned in the upcoming CI/CD & deployment phase. |

## Extras (optional "strong plus")

| Extra | Status | Evidence |
| --- | --- | --- |
| "Play All" — auto-play to the end and list results by week | Extra | `PlayAllRemainingFixturesAction` returns played fixtures grouped by week; `POST /api/league/play-all`; dashboard "Play All Remaining" with a confirmation modal. |
| Match results listed by week | Extra | `GET /api/fixtures` returns fixtures grouped by week; `FixtureWeekCard.vue` renders each week with scores and played/pending badges. |
| Edit match results and recalculate standings | Future | Not implemented. There is no endpoint or UI to edit a played fixture's score. Standings are always recomputed from stored fixtures, so adding an edit endpoint that re-derives the table would be a natural follow-up. |

## Notes

- **Prediction timing.** The brief says predictions appear "after the 4th week"; the
  FAQ phrases it as "entering the last 3 weeks". For a four-team double round-robin
  (6 weeks, 2 fixtures per week) these differ by one week. This project follows the
  brief: predictions unlock once **8 fixtures (week 4)** are played, or immediately
  when the season is complete (the leader is then 100%).
- **Deployment.** Live deployment and CI are intentionally deferred to a later phase;
  the repository itself is share-ready.
