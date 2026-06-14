#!/usr/bin/env bash
#
# Deploy Champions League Simulation on a native VPS (no Docker).
# Run as the deploy user; service reloads use sudo.
#
# Production is two-domain:
#   frontend  -> https://champions.ferzendervarli.com      (Vue dist)
#   API       -> https://api.champions.ferzendervarli.com  (Laravel/PHP-FPM)
#
#   ./deploy.sh
#
# Override defaults via env vars, e.g. APP_DIR=/srv/app API_DOMAIN=api.example.com ./deploy.sh

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/champions-league}"
BRANCH="${BRANCH:-main}"
API_DOMAIN="${API_DOMAIN:-api.champions.ferzendervarli.com}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.3-fpm}"

log() { printf '\n\033[1;32m==>\033[0m %s\n' "$1"; }

cd "$APP_DIR"

log "Pulling latest from origin/${BRANCH}"
git fetch --prune origin
PREVIOUS_COMMIT="$(git rev-parse HEAD)"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"
log "Deploying $(git rev-parse --short HEAD) (was ${PREVIOUS_COMMIT:0:7})"

log "Installing backend dependencies"
cd "$APP_DIR/backend"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

log "Clearing stale caches and running migrations"
php artisan optimize:clear
php artisan migrate --force

log "Rebuilding Laravel caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

log "Building frontend"
cd "$APP_DIR/frontend"
npm ci
# Vite empties the output directory on build, so stale assets are removed safely.
npm run build

log "Reloading PHP-FPM and Nginx"
sudo systemctl reload "$PHP_FPM_SERVICE"
sudo nginx -t
sudo systemctl reload nginx

log "Health check"
if curl -fsS -H "Accept: application/json" "https://${API_DOMAIN}/api/health" | grep -q '"status":"ok"'; then
    log "Deploy complete and healthy ✓"
else
    echo "Health check FAILED — investigate before serving traffic." >&2
    echo "Rollback: deployment/scripts/rollback.sh ${PREVIOUS_COMMIT}" >&2
    exit 1
fi
