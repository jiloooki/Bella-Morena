#!/usr/bin/env bash

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

if ! command -v php >/dev/null 2>&1; then
  echo "PHP CLI is required on the server." >&2
  exit 1
fi

PHP_CMD=(php -d allow_url_fopen=On)
COMPOSER_PHAR="/home/bellamorena/composer.phar"
COMPOSER_CMD=("${PHP_CMD[@]}" "$COMPOSER_PHAR")

if [ ! -f "$COMPOSER_PHAR" ]; then
  echo "Composer PHAR not found at $COMPOSER_PHAR." >&2
  exit 1
fi

if [ ! -f .env ]; then
  echo "Missing .env in $APP_DIR. Create it on the server before deploying." >&2
  exit 1
fi

mkdir -p \
  bootstrap/cache \
  storage/app/public \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs

maintenance_mode=0

if [ -f vendor/autoload.php ]; then
  if "${PHP_CMD[@]}" artisan down --retry=60; then
    maintenance_mode=1
  fi
fi

cleanup() {
  if [ "$maintenance_mode" -eq 1 ]; then
    "${PHP_CMD[@]}" artisan up || true
  fi
}

trap cleanup EXIT

echo "Using Composer command: ${COMPOSER_CMD[*]}"
"${COMPOSER_CMD[@]}" install --no-dev --prefer-dist --no-interaction --optimize-autoloader

"${PHP_CMD[@]}" artisan migrate --force
"${PHP_CMD[@]}" artisan storage:link || true
"${PHP_CMD[@]}" artisan optimize:clear
"${PHP_CMD[@]}" artisan config:cache
"${PHP_CMD[@]}" artisan route:cache
"${PHP_CMD[@]}" artisan view:cache

if [ "$maintenance_mode" -eq 1 ]; then
  "${PHP_CMD[@]}" artisan up
fi

trap - EXIT

echo "Deployment finished successfully."
