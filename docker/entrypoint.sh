#!/bin/sh
set -e

# Pastikan direktori database dan file SQLite ada
mkdir -p /var/www/html/database
if [ ! -f /var/www/html/database/database.sqlite ]; then
    touch /var/www/html/database/database.sqlite
fi

# Pastikan direktori storage dan cache ada
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Atur kepemilikan dan hak akses (775) untuk user web www-data
chown -R www-data:www-data /var/www/html/database
chmod -R 775 /var/www/html/database

chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/bootstrap/cache

# Buat symbolic link storage jika belum terhubung
php artisan storage:link --no-interaction || true

# Jalankan migrasi database jika RUN_MIGRATIONS aktif (default true)
if [ "$RUN_MIGRATIONS" = "true" ] || [ -z "$RUN_MIGRATIONS" ]; then
    php artisan migrate --force --no-interaction || true
fi

exec "$@"
