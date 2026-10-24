#!/bin/sh
set -e
cd /var/www/html

if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY belum diisi. Jalankan 'php artisan key:generate --show' di komputer lalu isi di Environment Render." >&2
  exit 1
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs

php artisan package:discover --ansi
php artisan storage:link --force || true
php artisan config:cache
php artisan migrate --force

# Seeding hanya kalau RUN_SEED=true (deploy pertama). Setelah itu set ke false.
if [ "$RUN_SEED" = "true" ]; then
  php artisan db:seed --force
fi

chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
