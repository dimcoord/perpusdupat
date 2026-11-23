# ==========================================
# Tahap 1: Build Frontend (Alpine.js & Vite)
# ==========================================
FROM node:22-alpine AS frontend
WORKDIR /app

COPY package*.json vite.config.js ./
RUN npm ci

COPY resources ./resources
COPY public ./public
RUN npm run build

# ==========================================
# Tahap 2: Runtime PHP 8.3 & Nginx
# ==========================================
FROM php:8.3-fpm-alpine

# Install dependensi sistem dasar
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    sqlite

# Install ekstensi PHP menggunakan script installer teruji (otomatis kelola build tools, dependensi C/C++, dan flags)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd zip bcmath intl opcache pdo_sqlite

# Salin Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Salin seluruh kode proyek
COPY . .

# Salin hasil kompilasi aset dari Tahap 1
COPY --from=frontend /app/public/build ./public/build

# Install dependensi PHP (production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Konfigurasi Nginx, Supervisor, dan Entrypoint
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Atur permission dasar
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
