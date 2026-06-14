# Native VPS Deployment

A classic, Docker-free production setup: **Cloudflare → Nginx → (Vue static + Laravel
PHP-FPM) → native PostgreSQL**, all on one host and one domain.

```
Cloudflare (proxy, TLS, cache)
        │
      Nginx ──────────────┬────────────────────────────
        │ /               │ /api
   Vue dist (static)   Laravel public/ via PHP-FPM 8.3
                              │
                        PostgreSQL (native)
```

The SPA and API share a single origin (`champions.ferzendervarli.com`), so **no CORS is
needed in production**. There is no queue worker (the app uses the `sync` queue), so no
`systemd` service is required.

## Prerequisites

Ubuntu/Debian VPS with:

- Nginx
- PHP 8.3 + `php8.3-fpm`, `php8.3-pgsql`, `php8.3-bcmath`, `php8.3-mbstring`, `php8.3-xml`, `php8.3-curl`
- Composer
- Node 22 + npm
- PostgreSQL 16
- A domain proxied by Cloudflare

## Directory layout

```
/var/www/champions-league/        # git checkout
├── backend/   (Laravel; public/ is the API web root)
└── frontend/  (Vue; dist/ is the static web root)
```

## First-time setup

```bash
# 1. Code
sudo mkdir -p /var/www && sudo chown "$USER":www-data /var/www
git clone https://github.com/fvarli/champions-league.git /var/www/champions-league
cd /var/www/champions-league

# 2. Database (run as the postgres superuser)
sudo -u postgres psql -c "CREATE USER champions_user WITH PASSWORD 'change-me';"
sudo -u postgres psql -c "CREATE DATABASE champions_league OWNER champions_user;"

# 3. Backend
cd backend
composer install --no-dev --optimize-autoloader
cp .env.production.example .env        # then edit secrets (DB_PASSWORD, etc.)
php artisan key:generate
php artisan migrate --force --seed     # seed once to create the four teams
php artisan config:cache && php artisan route:cache && php artisan view:cache
# Writable dirs for the web server
sudo chown -R www-data:www-data storage bootstrap/cache

# 4. Frontend
cd ../frontend
cp .env.production.example .env        # VITE_API_URL = site origin (no /api)
npm ci
npm run build

# 5. Nginx + TLS
sudo cp ../deployment/nginx/champions-league.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/champions-league.conf /etc/nginx/sites-enabled/
# Install a Cloudflare Origin Certificate at the paths in the conf, then:
sudo nginx -t && sudo systemctl reload nginx
```

Verify: `curl https://champions.ferzendervarli.com/api/health` returns `"status":"ok"`.

## Deploying updates

From `/var/www/champions-league`:

```bash
deployment/scripts/deploy.sh
```

It pulls `main`, installs prod deps, migrates, rebuilds Laravel caches, rebuilds the
frontend, reloads PHP-FPM + Nginx, and runs a health check. Override defaults with env
vars (`APP_DIR`, `BRANCH`, `DOMAIN`, `PHP_FPM_SERVICE`).

## Rolling back

```bash
deployment/scripts/rollback.sh            # previous commit
deployment/scripts/rollback.sh <commit>   # a specific commit/tag
```

Rollback reverts **code only** — database migrations are not undone automatically.

## Cloudflare DNS & settings checklist

- **DNS** — `A` record `champions` → VPS IP, **proxied** (orange cloud).
- **SSL/TLS** — mode **Full (strict)** with a Cloudflare Origin Certificate on the host.
- **Always Use HTTPS** — on.
- **HTTP/3 (with QUIC)** — on.
- **Brotli** — on (Cloudflare edge; optional origin module in the Nginx conf).
- **Caching** — cache static assets; the SPA's hashed `/assets/*` are immutable, while
  `index.html` is served `no-cache`.

## Two-domain variant (optional)

To split frontend and API onto separate hosts, give the API its own `server` block with
`root .../backend/public` and the standard Laravel `try_files $uri /index.php?$query_string`,
point the frontend at it via `VITE_API_URL`, and add the API origin to `config/cors.php`.

## If a queue worker is ever added

Create a `systemd` unit running `php artisan queue:work` and switch `QUEUE_CONNECTION`
away from `sync`. Not needed today.
