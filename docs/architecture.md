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
concerns out of the domain. No domain logic exists yet (Phase 1).

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
