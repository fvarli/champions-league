# Native VPS Deployment

A classic, Docker-free production setup: **Cloudflare → Nginx → (Vue static + Laravel
PHP-FPM) → native PostgreSQL**, on one host across **two domains**.

```
Cloudflare (proxy, TLS, cache)
        │
      Nginx
        ├── champions.ferzendervarli.com      → Vue dist (static)
        └── api.champions.ferzendervarli.com  → Laravel public/ via PHP-FPM 8.3
                                                       │
                                                 PostgreSQL (native)
```

Because the SPA and API are on **different origins**, CORS is **required** in production:
the API allows the frontend origin via `config/cors.php`, which reads the comma-separated
**`FRONTEND_URLS`** env var (it must include `https://champions.ferzendervarli.com`). There
is no queue worker (the app uses the `sync` queue), so no `systemd` service is required.

## Prerequisites

Ubuntu/Debian VPS with:

- Nginx
- PHP 8.3 + `php8.3-fpm`, `php8.3-pgsql`, `php8.3-bcmath`, `php8.3-mbstring`, `php8.3-xml`, `php8.3-curl`
- Composer
- Node 22 + npm
- PostgreSQL 16
- Two domains (or subdomains) pointed at the VPS via Cloudflare

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
sudo chown -R www-data:www-data storage bootstrap/cache

# 4. Frontend (VITE_API_URL = the API subdomain, no /api suffix)
cd ../frontend
cp .env.production.example .env
npm ci
npm run build

# 5. Nginx + TLS (two server blocks — see below), then:
sudo nginx -t && sudo systemctl reload nginx
```

Verify: `curl https://api.champions.ferzendervarli.com/api/health` returns `"status":"ok"`.

## Nginx (two-domain)

Two server blocks behind the HTTP→HTTPS redirect, each with a Cloudflare Origin
Certificate:

```nginx
# Frontend — champions.ferzendervarli.com
server {
    listen 443 ssl;
    server_name champions.ferzendervarli.com;
    root /var/www/champions-league/frontend/dist;
    index index.html;
    location / { try_files $uri $uri/ /index.html; }
    location /assets/ { expires 1y; add_header Cache-Control "public, immutable"; }
    location = /index.html { add_header Cache-Control "no-cache, no-store, must-revalidate"; }
}

# API — api.champions.ferzendervarli.com
server {
    listen 443 ssl;
    server_name api.champions.ferzendervarli.com;
    root /var/www/champions-league/backend/public;
    index index.php;
    location / { try_files $uri /index.php?$query_string; }
    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_hide_header X-Powered-By;
    }
}
```

(Copy the TLS/security/gzip directives from `nginx/champions-league.conf`.)

## Deploying updates

**Automated (CI/CD):** pushing to `main` runs [CI](../.github/workflows/ci.yml); on success
the [deploy workflow](../.github/workflows/deploy.yml) SSHes in and runs the steps below. It
needs repository secrets `PROD_HOST`, `PROD_USER`, `PROD_SSH_KEY`, and optionally
`PROD_SSH_PORT`.

**Manual:** from `/var/www/champions-league`, `deployment/scripts/deploy.sh` performs the
same pull → install → migrate → cache → build → reload → health-check flow.

## Rolling back

```bash
deployment/scripts/rollback.sh            # previous commit
deployment/scripts/rollback.sh <commit>   # a specific commit/tag
```

Rollback reverts **code only** — database migrations are not undone automatically.

## Cloudflare DNS & settings checklist

- **DNS** — two `A` records → VPS IP: `champions` (frontend) and `api.champions` (API).
- **SSL/TLS** — mode **Full (strict)** with a Cloudflare Origin Certificate on the host.
- **Always Use HTTPS** — on.
- **HTTP/3 (with QUIC)** — on.
- **Brotli** — on (Cloudflare edge; optional origin module in Nginx).
- **Caching** — cache static assets; hashed `/assets/*` are immutable, `index.html` is `no-cache`.

> If proxying the API subdomain through Cloudflare (orange cloud) causes SSL/origin-cert
> trouble, setting the `api` record to **DNS-only** (grey cloud) is acceptable — the API
> still serves TLS directly from the origin certificate.

## If a queue worker is ever added

Create a `systemd` unit running `php artisan queue:work` and switch `QUEUE_CONNECTION`
away from `sync`. Not needed today.

## Alternative: single-domain setup

Not the production path, but supported: serve the SPA at `/` and the API at `/api` from a
single host/server block (no CORS needed since they share an origin). The committed
[`nginx/champions-league.conf`](nginx/champions-league.conf) is a ready-made example, and
`frontend/.env` would then point `VITE_API_URL` at that same origin.
