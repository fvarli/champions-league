# Contributing

Thanks for your interest in improving **Champions League Simulation**.

## Local setup

See the [README](README.md#local-setup). In short:

```bash
# backend
cd backend && composer install && cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed && php artisan serve

# frontend
cd frontend && npm install && cp .env.example .env && npm run dev
```

## Development workflow

1. Create a branch from `main`.
2. Make a focused change, with tests where it makes sense.
3. Run the quality checks below.
4. Open a pull request using the template; CI must be green.

## Branch naming

`<type>/<short-description>` — e.g. `feat/edit-results`, `fix/standings-tiebreak`, `docs/api`.

## Commit convention

[Conventional Commits](https://www.conventionalcommits.org/): `feat:`, `fix:`, `chore:`,
`docs:`, `refactor:`, `test:`, `style:`. Keep messages short and imperative.

## Testing & quality

```bash
# backend
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse

# frontend
npm run lint
npm run type-check
npm run build
```

CI runs all of these on every push to `main` and on every pull request.

## Coding style

- **PHP** — Laravel Pint (`laravel` preset) and PHPStan level 6 (Larastan). Keep business
  logic in services/actions; controllers stay thin.
- **TypeScript / Vue** — ESLint + Prettier (`npm run lint`, `npm run format`). State lives in
  Pinia; components stay presentational.

## Documentation

Update the relevant docs (`README.md`, `docs/`) whenever behavior or the API changes.
