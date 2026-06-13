# Smoke QA

Manual checklist to confirm the environment is healthy after setup or a
significant change.

## Environment

```bash
docker compose up -d --build
```

1. **Database** — `docker compose ps` shows `db` as `healthy`.
2. **Backend health** — `curl -i http://localhost:8080/up` returns `200`.
3. **Frontend** — open `http://localhost:5173`; the placeholder page renders
   with Tailwind styling applied.
4. **Backend → DB** — `docker compose exec backend php artisan migrate:status`
   connects to PostgreSQL without error.

## League fixtures

With the four teams seeded (`php artisan migrate --seed`):

```bash
php artisan league:generate-fixtures
```

- Reports `Fixtures generated successfully.` and creates 12 rows in `matches`
  across weeks 1–6.
- Running it a second time fails with a clear message and creates no duplicates.

## Quality gates

Run before committing:

```bash
# backend
cd backend
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
php artisan test

# frontend
cd frontend
npm run lint
npm run type-check
npm run build
```

All commands should exit `0`.
