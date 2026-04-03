#!/usr/bin/env bash

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

if ! command -v php >/dev/null 2>&1; then
  echo "PHP CLI is required on the server." >&2
  exit 1
fi

COMPOSER_CMD=()

if command -v composer >/dev/null 2>&1; then
  COMPOSER_CMD=("$(command -v composer)")
else
  for candidate in \
    /usr/local/bin/composer \
    /opt/cpanel/composer/bin/composer \
    "$HOME/bin/composer"
  do
    if [ -x "$candidate" ]; then
      COMPOSER_CMD=("$candidate")
      break
    fi
  done

  if [ "${#COMPOSER_CMD[@]}" -eq 0 ]; then
    for phar in \
      "$APP_DIR/composer.phar" \
      "$HOME/composer.phar"
    do
      if [ -f "$phar" ]; then
        COMPOSER_CMD=(php "$phar")
        break
      fi
    done
  fi
fi

if [ "${#COMPOSER_CMD[@]}" -eq 0 ]; then
  echo "Composer is required on the server. Checked PATH, /usr/local/bin/composer, /opt/cpanel/composer/bin/composer, and composer.phar." >&2
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
  if php artisan down --retry=60; then
    maintenance_mode=1
  fi
fi

cleanup() {
  if [ "$maintenance_mode" -eq 1 ]; then
    php artisan up || true
  fi
}

trap cleanup EXIT

echo "Using Composer command: ${COMPOSER_CMD[*]}"
"${COMPOSER_CMD[@]}" install --no-dev --prefer-dist --no-interaction --optimize-autoloader

php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "$maintenance_mode" -eq 1 ]; then
  php artisan up
fi

trap - EXIT

echo "Deployment finished successfully."
