# ==========================================
# STAGE 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS frontend

WORKDIR /app

# Install dependensi npm terlebih dahulu agar layer di-cache
COPY package*.json ./
RUN npm ci || npm install

# Copy seluruh source code untuk proses kompilasi Vite
COPY . .
RUN npm run build

# ==========================================
# STAGE 2: PHP 8.3 & FrankenPHP Runtime
# ==========================================
FROM dunglas/frankenphp:1-php8.3-alpine

# Install PHP extensions yang dibutuhkan Laravel
RUN install-php-extensions \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    bcmath \
    gd \
    zip \
    pcntl \
    opcache \
    intl

# Install Composer binary resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Set environment variables produksi
ENV APP_ENV=production \
    APP_DEBUG=false \
    PORT=8080

# Install dependensi composer (cacheable layer)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --optimize-autoloader

# Copy kode aplikasi
COPY . .

# Ambil hasil kompilasi Vite dari Stage 1
COPY --from=frontend /app/public/build ./public/build

# Optimasi autoloader Composer
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Copy Caddyfile dan Entrypoint Script
COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Pastikan script berformat LF (UNIX) dan executable
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh

# Set permission storage dan cache Laravel
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
