#!/usr/bin/env bash
set -euo pipefail

# Colors
Y="\033[33m";G="\033[32m";R="\033[31m";C="\033[36m";Z="\033[0m"

cd /var/www/html

if [ ! -f artisan ]; then
  echo -e "${C}Laravel project not found. Creating...${Z}"
  composer create-project --prefer-dist --no-interaction laravel/laravel .
  echo -e "${G}Base Laravel created.${Z}"
  echo -e "${C}Installing Sanctum...${Z}"
  composer require laravel/sanctum
  php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
fi

# Apply overlay (custom controllers, routes, models)
if [ -d /opt/app-overlay ]; then
  echo -e "${C}Applying overlay files...${Z}"
  cp -r /opt/app-overlay/* .
fi

# Ensure .env
if [ ! -f .env ]; then
  echo -e "${C}Creating .env...${Z}"
  cp .env.example .env || true
  sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
  sed -i "s/DB_HOST=.*/DB_HOST=${DB_HOST:-postgres}/" .env
  sed -i "s/DB_PORT=.*/DB_PORT=${DB_PORT:-5432}/" .env
  sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE:-tumi}/" .env
  sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME:-tumi}/" .env
  sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD:-tumi_pwd}/" .env
  php artisan key:generate
fi

# Wait for Postgres
until pg_isready -h "${DB_HOST:-postgres}" -p "${DB_PORT:-5432}" -q; do
  echo "${Y}Waiting for Postgres...${Z}"; sleep 2;
done

# Run migrations
php artisan migrate --force || echo -e "${Y}Migrations skipped (possibly already run).${Z}"

# Cache config for speed (non-fatal if fails)
php artisan config:cache || true

echo -e "${G}Starting Laravel dev server on 0.0.0.0:8000 ...${Z}"
exec php artisan serve --host=0.0.0.0 --port=8000
