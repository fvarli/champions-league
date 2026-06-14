# Champions League Simulation

[![CI](https://github.com/fvarli/champions-league/actions/workflows/ci.yml/badge.svg)](https://github.com/fvarli/champions-league/actions/workflows/ci.yml)
![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Vue 3](https://img.shields.io/badge/Vue-3-42B883?logo=vuedotjs&logoColor=white)
![TypeScript](https://img.shields.io/badge/TypeScript-3178C6?logo=typescript&logoColor=white)
![PostgreSQL 16](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql&logoColor=white)
![PHPStan level 6](https://img.shields.io/badge/PHPStan-level%206-2A6F97)
[![License: MIT](https://img.shields.io/badge/License-MIT-green)](LICENSE)

> A football league simulator with double round-robin scheduling, a strength-based
> match engine, live standings, and a championship prediction engine — served by a
> Laravel REST API and a polished Vue 3 dashboard.

**[🟢 Live demo](https://champions.ferzendervarli.com)** · [▶ Watch the clip](#demo) · [Features](#features) · [Screenshots](#screenshots) · [Architecture](#architecture) · [Local setup](#local-setup)

<p align="center">
  <img src="docs/social-preview.png" alt="Champions League Simulation" width="820" />
</p>

---

## Features

- **League simulation** — run a full season for four teams from a clean slate.
- **Double round-robin fixtures** — 12 matches across 6 weeks, each pair home and away.
- **Match simulation engine** — results driven by team strength, home advantage, and controlled randomness.
- **Live standings** — points, goal difference, and tiebreakers recomputed from played fixtures.
- **Prediction engine** — estimates each team's championship probability from the 4th week onward.
- **Editable results** — edit any fixture's score; standings and predictions recalculate automatically.
- **REST API** — clean JSON endpoints with meaningful status codes.
- **Vue dashboard** — responsive, dark, premium analytics UI with toasts, skeletons, and transitions.
- **Installable PWA** — web manifest, icons, and a custom favicon; add it to your home screen.
- **Laravel backend** — service/action layered architecture, covered by automated tests.

> Internally, the prediction engine runs a **Monte Carlo** simulation: it replays the
> remaining fixtures 1000 times with the live match engine and reports how often each
> team finishes first.

---

## Demo

<p align="center">
  <img src="docs/demo.gif" alt="Champions League Simulation walkthrough" width="900" />
</p>

Open the dashboard → generate fixtures → play weeks → predictions unlock → play all → champion crowned.

---

## Screenshots

| Dashboard | Fixtures generated |
| --- | --- |
| ![Dashboard](docs/screenshots/01-dashboard.png) | ![Fixtures generated](docs/screenshots/02-fixtures-generated.png) |

| Week played | Championship prediction |
| --- | --- |
| ![Week played](docs/screenshots/03-week-played.png) | ![Prediction](docs/screenshots/04-prediction.png) |

| Edit a result | Recalculated standings |
| --- | --- |
| ![Edit result](docs/screenshots/08-edit-result.png) | ![Edited standings](docs/screenshots/09-edited-standings.png) |

| Play all (confirm) | Final table |
| --- | --- |
| ![Play all](docs/screenshots/05-play-all.png) | ![Final table](docs/screenshots/06-final-table.png) |

| Champion |
| --- |
| ![Champion](docs/screenshots/07-champion.png) |

---

## Architecture

```mermaid
flowchart LR
    UI["Vue 3 SPA<br/>Pinia · Tailwind v4"]

    subgraph API["Laravel 12 API"]
        direction LR
        C["Controllers<br/>+ Resources"] --> A["Actions<br/>orchestration"]
        A --> S["Services<br/>domain logic"]
        S --> M["Eloquent Models"]
    end

    UI -->|"REST /api"| C
    M --> DB[("PostgreSQL")]
```

Controllers stay thin and translate domain exceptions to HTTP status codes. Actions
orchestrate multi-step flows (play week / next / all) over the services. Services hold
the football logic — fixture generation, simulation, standings, and prediction — and
return immutable value objects. Eloquent persists only raw facts; nothing derived is
stored.

---

## API

All endpoints live under `/api` and return JSON. See [docs/api.md](docs/api.md) for
full examples.

| Method | Path                     | Description                          |
| ------ | ------------------------ | ------------------------------------ |
| GET    | `/api/health`            | Liveness/readiness probe (checks DB) |
| GET    | `/api/teams`             | List teams                           |
| GET    | `/api/fixtures`          | Fixtures grouped by week             |
| GET    | `/api/standings`         | Current league table                 |
| POST   | `/api/fixtures/generate` | Generate the schedule                |
| PATCH  | `/api/fixtures/{id}/score` | Edit a fixture's score (0–20)      |
| POST   | `/api/weeks/{week}/play` | Play a specific week                 |
| POST   | `/api/weeks/next/play`   | Play the earliest unplayed week      |
| POST   | `/api/league/play-all`   | Play all remaining fixtures          |
| POST   | `/api/league/reset`      | Reset to seeded teams, clear fixtures|
| GET    | `/api/predictions`       | Championship chances                 |

**Reliability** — every response carries an `X-Request-Id` header (and a matching
`request_id` in the JSON body); a supplied `X-Request-Id` is reused for correlation.
`/api/*` always returns JSON, sends conservative security headers, and is rate limited
to **60 requests per minute per IP** (`429` when exceeded).

**Demo reset** — clear fixtures and restore the four seeded teams for a clean
walkthrough. Use the dashboard's **Reset Season** button, the API
(`POST /api/league/reset`), or the CLI:

```bash
php artisan league:demo-reset
```

---

## Tech Stack

**Backend**

- Laravel 12
- PHP 8.3
- PostgreSQL

**Frontend**

- Vue 3
- TypeScript
- Pinia
- Tailwind CSS v4
- Vite

**Quality**

- PHPUnit
- PHPStan (Larastan, level 6)
- Laravel Pint
- ESLint

**Infrastructure**

- Docker Compose
- Nginx

---

## Local setup

**Backend**

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

**Frontend**

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

The dashboard runs at `http://localhost:5173` and calls the API at
`http://127.0.0.1:8000` (configurable via `VITE_API_URL`).

> Prefer containers? `docker compose up --build` starts PostgreSQL, the API, Nginx, and the frontend.

---

## Deployment

Production runs as a classic **native VPS** stack — **no Docker required**:
**Cloudflare → Nginx → (Vue static + Laravel PHP-FPM 8.3) → native PostgreSQL**, across two
domains:

- **Frontend** — <https://champions.ferzendervarli.com>
- **API** — <https://api.champions.ferzendervarli.com>

Because the SPA and API are on different origins, the API allows the frontend via CORS
(`FRONTEND_URLS`). The full runbook, Nginx config, and scripts live in
[`deployment/`](deployment/README.md).

```bash
# on the server, from /var/www/champions-league
deployment/scripts/deploy.sh           # pull, build, migrate, cache, reload, health check
deployment/scripts/rollback.sh         # revert to the previous commit
```

Pushing to `main` runs [CI](.github/workflows/ci.yml); on success the
[deploy workflow](.github/workflows/deploy.yml) ships over SSH. It requires repository
**secrets**: `PROD_HOST`, `PROD_USER`, `PROD_SSH_KEY`, and (optional) `PROD_SSH_PORT`.

**Cloudflare checklist:** `A` records for `champions` and `api.champions` · SSL **Full
(strict)** · **Always Use HTTPS** · **HTTP/3** · **Brotli** · cache hashed `/assets/*`
(immutable), `index.html` `no-cache`.

---

## Testing

**Backend**

```bash
php artisan test
./vendor/bin/phpstan analyse
./vendor/bin/pint --test
```

**Frontend**

```bash
npm run lint
npm run type-check
npm run build
```

---

## Project structure

```
champions-league/
├── backend/                       # Laravel 12 API
│   ├── app/
│   │   ├── Actions/               # PlayWeek / PlayNextWeek / PlayAllRemaining
│   │   ├── Exceptions/            # Domain exceptions (HTTP-status aware)
│   │   ├── Http/
│   │   │   ├── Controllers/Api/   # LeagueController
│   │   │   └── Resources/         # Team / Fixture / Standing / Prediction
│   │   ├── Models/                # Team, Fixture
│   │   └── Services/              # Fixtures, Simulation, Standings, Prediction
│   ├── routes/api.php
│   └── tests/Feature/             # PHPUnit feature tests
├── frontend/                      # Vue 3 + TypeScript SPA
│   └── src/
│       ├── components/            # Dashboard UI components
│       ├── services/              # HTTP client + typed API
│       ├── stores/                # Pinia (league, toasts)
│       ├── types/                 # API response types
│       └── views/HomeView.vue
├── docker/                        # Dockerfiles + Nginx config
├── docs/                          # Architecture, API, screenshots, demo
└── docker-compose.yml
```

---

## Design decisions

**Service layer** — All football logic lives in single-responsibility services
(`FixtureGenerationService`, `MatchSimulationService`, `LeagueStandingsService`,
`ChampionshipPredictionService`). Controllers and actions never contain business rules,
which keeps the domain testable in isolation and reusable across the API and CLI.

**Actions** — Multi-step flows are modelled as thin, composable actions
(`PlayWeekAction`, `PlayNextWeekAction`, `PlayAllRemainingFixturesAction`). They
orchestrate the simulation service and enforce flow rules (valid week, already played,
league complete) without duplicating simulation logic.

**Immutable value objects** — Standings and predictions are returned as readonly value
objects (`TeamStanding`, `ChampionChance`, `MatchResult`) rather than mutated arrays. A
separate mutable `TeamTally` accumulates during calculation, so results are computed on
the fly and never persisted, keeping the database the single source of raw truth.

**Monte Carlo prediction** — Championship odds come from simulating the remaining
fixtures many times (default 1000) using the same scoring logic as live matches, then
counting how often each team finishes first. Randomness is injected, so tests run with a
seeded engine for stable, deterministic assertions.

**Deterministic fixture generation** — The schedule is built with the circle method, so
the same teams always yield the same fixtures. Generation refuses to run twice, giving a
predictable starting point for both gameplay and tests.

---

## Future improvements

- Authentication and per-user leagues
- Multiple seasons and historical records
- Team management (create, edit, rename)
- Player transfers affecting team strength
- REST pagination and filtering
- CI/CD pipeline with automated deployment

How each assignment requirement maps to the implementation is tracked in the
[requirements audit](docs/requirements-audit.md).

---

## Contributing & security

Contributions are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md) for the workflow and
quality gates (all enforced by [CI](.github/workflows/ci.yml)). To report a vulnerability,
follow [SECURITY.md](SECURITY.md).

---

## License

Released under the [MIT License](LICENSE).
