# API

## Conventions

- Base URL (local): `http://localhost:8080`
- All endpoints live under `/api` and return JSON.
- Reads return `{ "data": ... }`. Actions return `{ "message": ..., "data": ... }`.
- No authentication is required.

## Status codes

| Code | Meaning                                                                 |
| ---- | ----------------------------------------------------------------------- |
| 200  | Successful read or action                                               |
| 201  | Fixtures generated                                                      |
| 409  | Conflict: fixtures already generated, week already played, league done  |
| 422  | Invalid week, or prediction not yet available                           |

Errors return `{ "message": "..." }`.

## Endpoints

| Method | Path                     | Description                            |
| ------ | ------------------------ | -------------------------------------- |
| GET    | `/api/teams`             | List teams                             |
| GET    | `/api/fixtures`          | List fixtures grouped by week          |
| GET    | `/api/standings`         | Current league table                   |
| POST   | `/api/fixtures/generate` | Generate the full schedule (12 matches)|
| POST   | `/api/weeks/{week}/play` | Play a specific week                   |
| POST   | `/api/weeks/next/play`   | Play the earliest unplayed week        |
| POST   | `/api/league/play-all`   | Play all remaining fixtures            |
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
