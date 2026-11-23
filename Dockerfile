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

# Install dependensi sistem dan ekstensi PHP
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    sqlite \
    sqlite-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_sqlite \
        sqlite3 \
        gd \
        zip \
        bcmath \
        intl \
        mbstring \
        xml \
        opcache

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
