# Champions League

A football league simulation built as a decoupled Laravel API and Vue single-page
application.

> **Status:** Phase 1 — project foundation. No domain logic yet.

## Stack

| Layer    | Technology                                          |
| -------- | --------------------------------------------------- |
| Backend  | Laravel 12, PHP 8.3, PostgreSQL 16                  |
| Frontend | Vue 3, TypeScript, Vite, Pinia, Tailwind CSS v4     |
| Quality  | Laravel Pint, PHPStan (Larastan), PHPUnit, ESLint   |
| Infra    | Docker Compose, GitHub Actions                      |

## Repository layout

```
backend/    Laravel API
frontend/   Vue SPA
docker/     Dockerfiles and nginx config
docs/       Architecture, API and QA notes
```

## Getting started

### Docker (recommended)

```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
docker compose up -d --build
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate
```

- API: <http://localhost:8080>
- Frontend: <http://localhost:5173>

### Local

```bash
# backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan serve

# frontend
cd frontend
npm install
cp .env.example .env
npm run dev
```

## Quality

```bash
# backend
cd backend
./vendor/bin/pint          # format
./vendor/bin/phpstan analyse
php artisan test

# frontend
cd frontend
npm run lint
npm run type-check
npm run build
```

## Documentation

- [Architecture](docs/architecture.md)
- [API](docs/api.md)
- [Smoke QA](docs/smoke-qa.md)
