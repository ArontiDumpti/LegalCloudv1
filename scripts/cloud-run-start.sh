#!/bin/sh
set -eu

cd /var/www/html

PORT="${PORT:-8080}"
export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
export APP_NAME="${APP_NAME:-LegalCloud AI}"
export APP_URL="${APP_URL:-http://0.0.0.0:${PORT}}"
export DB_CONNECTION=sqlite
export DB_DATABASE="${DB_DATABASE:-/tmp/legalcloud.sqlite}"
export SESSION_DRIVER=file
export CACHE_STORE=file
export QUEUE_CONNECTION=sync
export LOG_CHANNEL=stderr
export FILESYSTEM_DISK=public
export FORCE_HTTPS="${FORCE_HTTPS:-true}"

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/public
touch "$DB_DATABASE"

if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="$(php artisan key:generate --show)"
fi

php artisan storage:link --force >/dev/null 2>&1 || true
php artisan migrate --force
php artisan db:seed --force

exec php artisan serve --host=0.0.0.0 --port="$PORT"
