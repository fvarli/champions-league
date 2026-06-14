# Architecture

## Overview

A monorepo split into two independently deployable applications:

| Path        | Stack                                   | Responsibility                  |
| ----------- | --------------------------------------- | ------------------------------- |
| `backend/`  | Laravel 12, PHP 8.3, PostgreSQL         | JSON API and domain logic       |
| `frontend/` | Vue 3, TypeScript, Vite, Pinia, Tailwind | Single-page application (SPA)   |

The two communicate over HTTP/JSON. The frontend reads the API base URL from
`VITE_API_URL` and holds no server-side coupling.

## Backend layering

Controllers stay thin and delegate to a service/action layer:

```
HTTP request → Controller → Service / Action → Model → PostgreSQL
```

- `app/Http` — controllers, requests, middleware.
- `app/Services` — reusable, stateful domain operations.
- `app/Actions` — single-purpose use cases.
- `app/Models` — Eloquent models.

These boundaries keep business logic out of controllers and framework
concerns out of the domain.

## Prediction engine

The championship prediction engine estimates each team's probability of finishing
first. It is implemented with a **Monte Carlo** simulation: the remaining fixtures
are simulated repeatedly (1000 runs by default) using the same match engine as live
games. After each run the league table is recalculated and the champion recorded; a
team's probability is the share of runs in which it finished first. Randomness is
injected, so tests drive it with a seeded engine for deterministic results.

## API reliability

A small set of middleware hardens the API without touching domain logic: a
request-id middleware adds an `X-Request-Id` correlation id (reused if supplied)
and merges it into JSON bodies from one central place; a force-JSON middleware
keeps `/api/*` responses JSON even for framework errors; and a security-headers
middleware applies conservative headers. API routes are rate limited to 60
requests/minute per IP. A `GET /api/health` endpoint checks database
connectivity, and `POST /api/league/reset` (or `php artisan league:demo-reset`)
restores a clean demo state.

## API observability

Every API response carries an `X-Request-Id` correlation id (see *API reliability*
above). To make that id useful after the fact, an `ApiAccessLogMiddleware` persists a
lightweight row per `/api/*` request into the `api_access_logs` table via
`ApiAccessLogService`: the `request_id`, HTTP method, path (no query string), route
name, status code, duration, client IP, and user agent. The same `request_id` returned
in the response can later be searched in that table, so a production incident or demo
issue can be traced end to end.

This is **observability, not analytics**: no request body, no response body, and no
other payload is ever stored — only request metadata. Logging is best-effort and
self-contained; a persistence failure is caught and reported, never surfaced to the
caller, so it cannot break an API response. Error responses (e.g. a `404`) are logged
too. Retention is unbounded for now; a scheduled prune can be added if traffic grows.

## Frontend structure

```
src/
  router/    routes
  views/     route-level components
  components/ reusable UI (added as needed)
  stores/    Pinia state
  services/  API access (fetch wrapper in services/http.ts)
  types/     shared TypeScript types (added as needed)
```

## Runtime

PostgreSQL 16 is the application database. Docker Compose runs the canonical
stack on **PHP 8.3** (`db`, `backend`, `nginx`, `frontend`). The host may run
PHP 8.2 for tooling; `composer.json` keeps Laravel's default `^8.2` constraint
so Pint, PHPStan, and the test suite run on either version.

The automated test suite uses an in-memory SQLite database for speed and
isolation; PostgreSQL remains the runtime and integration target.
