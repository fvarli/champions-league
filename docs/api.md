# API

## Conventions

- Base URL (local): `http://127.0.0.1:8000`
- All endpoints live under `/api` and return JSON.
- Reads return `{ "data": ... }`. Actions return `{ "message": ..., "data": ... }`.
- No authentication is required.

### Reliability

- **Request id** — every response carries an `X-Request-Id` header, and JSON
  bodies include a matching `request_id`. Send your own `X-Request-Id` to have it
  reused for correlation.
- **Forced JSON** — `/api/*` always responds with JSON, even for framework errors.
- **Security headers** — responses set `X-Content-Type-Options`, `X-Frame-Options`,
  `Referrer-Policy`, `Permissions-Policy`, and the `Cross-Origin-*-Policy` headers.
- **Rate limiting** — 60 requests per minute per IP. Exceeding it returns `429`.

## Status codes

| Code | Meaning                                                                 |
| ---- | ----------------------------------------------------------------------- |
| 200  | Successful read or action                                               |
| 201  | Fixtures generated                                                      |
| 409  | Conflict: fixtures already generated, week already played, league done  |
| 422  | Invalid week, or prediction not yet available                           |
| 429  | Too many requests (rate limit exceeded)                                 |
| 503  | Health check failed (database unavailable)                              |

Errors return `{ "message": "..." }`.

## Endpoints

| Method | Path                     | Description                            |
| ------ | ------------------------ | -------------------------------------- |
| GET    | `/api/health`            | Liveness/readiness probe (checks DB)   |
| GET    | `/api/teams`             | List teams                             |
| GET    | `/api/fixtures`          | List fixtures grouped by week          |
| GET    | `/api/standings`         | Current league table                   |
| POST   | `/api/fixtures/generate` | Generate the full schedule (12 matches)|
| PATCH  | `/api/fixtures/{fixture}/score` | Edit a fixture's score          |
| POST   | `/api/weeks/{week}/play` | Play a specific week                   |
| POST   | `/api/weeks/next/play`   | Play the earliest unplayed week        |
| POST   | `/api/league/play-all`   | Play all remaining fixtures            |
| POST   | `/api/league/reset`      | Reset to seeded teams, clear fixtures  |
| GET    | `/api/predictions`       | Championship chances (after 8 played)  |

## Example responses

`GET /api/standings`

```json
{
  "data": [
    {
      "team": { "id": 1, "name": "Liverpool", "strength": 90 },
      "played": 6, "won": 4, "drawn": 1, "lost": 1,
      "goals_for": 11, "goals_against": 5, "goal_difference": 6, "points": 13
    }
  ]
}
```

`GET /api/fixtures` (grouped by week)

```json
{
  "data": {
    "1": [
      {
        "id": 1, "week": 1,
        "home_team": { "id": 1, "name": "Liverpool", "strength": 90 },
        "away_team": { "id": 4, "name": "Arsenal", "strength": 80 },
        "home_score": 2, "away_score": 1,
        "played_at": "2026-06-13T18:00:00.000000Z", "is_played": true
      }
    ]
  }
}
```

`POST /api/weeks/1/play`

```json
{ "message": "Week 1 played.", "data": [ { "id": 1, "week": 1, "is_played": true } ] }
```

`GET /api/predictions`

```json
{ "data": [ { "team": { "id": 1, "name": "Liverpool", "strength": 90 }, "percentage": 62.5 } ] }
```

`GET /api/health`

```json
{
  "status": "ok",
  "app": "Champions League",
  "database": "ok",
  "timestamp": "2026-06-14T04:18:28.000000Z",
  "request_id": "2d57630f-86a7-4ed8-9f33-b3233a3ec744"
}
```

If the database is unreachable, it returns `503` with
`{ "status": "error", "database": "unavailable", "message": "...", "request_id": "..." }`.

`POST /api/league/reset`

```json
{
  "message": "League reset to its initial state.",
  "data": { "teams": [ ... ], "fixtures": [], "standings": [ ... ] }
}
```

`PATCH /api/fixtures/{fixture}/score`

Body: `{ "home_score": 2, "away_score": 1 }` (each an integer 0–5; invalid input returns
`422`). Editing an unplayed fixture marks it played; an already played fixture keeps its
original `played_at`. Standings always recompute from fixtures, so the response returns
the refreshed table (and predictions when available).

```json
{
  "message": "Fixture score updated.",
  "data": {
    "fixture": { "id": 1, "home_score": 2, "away_score": 1, "is_played": true, ... },
    "standings": [ ... ],
    "predictions": []
  }
}
```

When predictions are not yet available the response includes `"predictions": []` and a
`"prediction_notice"` string instead of failing.
