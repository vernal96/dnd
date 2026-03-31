#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ ! -f composer.json ]; then
  echo "composer.json not found in /var/www/html"
  exit 1
fi

if [ ! -f .env ]; then
  cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

if ! grep -q "^APP_KEY=base64:" .env; then
  php artisan key:generate --force
fi

php artisan config:clear >/dev/null 2>&1 || true

if [ "${APP_ENV:-local}" = "local" ]; then
  php artisan migrate --force
  php artisan app:ensure-local-users --no-interaction
fi

exec "$@"
