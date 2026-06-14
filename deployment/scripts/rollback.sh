#!/usr/bin/env bash
#
# Roll back Champions League Simulation to a previous commit on a native VPS.
#
#   ./rollback.sh            # roll back to the previous commit (HEAD~1)
#   ./rollback.sh <commit>   # roll back to a specific commit/tag
#
# Note: this reverts CODE only. It does NOT undo database migrations — if the
# release you are leaving added migrations, review them manually.

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/champions-league}"
API_DOMAIN="${API_DOMAIN:-api.champions.ferzendervarli.com}"
PHP_FPM_SERVICE="${PHP_FPM_SERVICE:-php8.3-fpm}"
TARGET="${1:-HEAD~1}"

log() { printf '\n\033[1;33m==>\033[0m %s\n' "$1"; }

cd "$APP_DIR"

# Deploy may run as root against a repo owned by deploy:www-data.
git config --global --add safe.directory "$APP_DIR"

log "Rolling back to ${TARGET} (current $(git rev-parse --short HEAD))"
git fetch --prune origin
git reset --hard "$TARGET"
log "Now at $(git rev-parse --short HEAD)"

log "Reinstalling backend dependencies"
cd "$APP_DIR/backend"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "WARNING: database migrations are not rolled back automatically." >&2
echo "         If needed, run a targeted 'php artisan migrate:rollback' manually." >&2

log "Clearing stale caches and rebuilding them"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

log "Rebuilding frontend"
cd "$APP_DIR/frontend"
npm ci
npm run build

# Ensure the web server (www-data) can read the built SPA.
find "$APP_DIR/frontend/dist" -type d -exec chmod 755 {} \;
find "$APP_DIR/frontend/dist" -type f -exec chmod 644 {} \;

log "Reloading PHP-FPM and Nginx"
sudo systemctl reload "$PHP_FPM_SERVICE"
sudo nginx -t
sudo systemctl reload nginx

log "Health check"
if curl -fsS -H "Accept: application/json" "https://${API_DOMAIN}/api/health" | grep -q '"status":"ok"'; then
    log "Rollback complete and healthy ✓"
else
    echo "Health check FAILED after rollback — investigate immediately." >&2
    exit 1
fi
