#!/usr/bin/env bash

set -euo pipefail

cd "$(dirname "$0")/.."

maintenance_enabled=0
finish() {
    if [ "$maintenance_enabled" -eq 1 ]; then
        php artisan up || true
    fi
}
trap finish EXIT

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views
mkdir -p storage/logs bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

php artisan down --retry=60
maintenance_enabled=1

git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction

php artisan optimize:clear
php artisan migrate --force

if [ ! -e public/storage ]; then
    php artisan storage:link
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan system:mark-deployed

php artisan up
maintenance_enabled=0

echo "Deployment PAS selesai. Periksa menu Kesehatan Sistem."
