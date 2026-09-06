#!/bin/sh
set -e

echo "==> Starting GeoCrime-Web container initialization..."

# Validasi APP_URL: cegah crash 'Invalid URI' jika domain Railway belum di-generate
case "$APP_URL" in
    ""|"http://"|"https://"|"http:/"|"https:/")
        echo "==> APP_URL belum valid ('$APP_URL'). Menggunakan fallback 'http://localhost'..."
        export APP_URL="http://localhost"
        ;;
esac

# Pastikan permission folder storage dan bootstrap cache aman
mkdir -p /app/storage/framework/cache
mkdir -p /app/storage/framework/sessions
mkdir -p /app/storage/framework/views
mkdir -p /app/storage/logs
mkdir -p /app/bootstrap/cache

chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null || true
chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true

# Pastikan symbolic link storage publik terpasang
echo "==> Ensuring public storage link exists..."
php artisan storage:link --force || true

# Cek APP_KEY
if [ -z "$APP_KEY" ]; then
    echo "================================================================="
    echo "PERINGATAN: APP_KEY belum diisi di Railway Dashboard Variables!"
    echo "Harap generate key lokal dengan 'php artisan key:generate --show'"
    echo "dan masukkan ke tab Variables di Railway."
    echo "================================================================="
fi

# Optimasi cache Laravel pada runtime container
if [ "$APP_ENV" = "production" ] || [ -n "$APP_KEY" ]; then
    echo "==> Optimizing Laravel cache..."
    php artisan config:clear || true
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Jalankan migrasi otomatis jika RUN_MIGRATIONS bernilai true
if [ "$RUN_MIGRATIONS" = "true" ] || [ "$AUTORUN_MIGRATIONS" = "true" ]; then
    echo "==> Running database migrations..."
    php artisan migrate --force || echo "==> Peringatan: Migrasi gagal atau database belum siap."
fi

# Jalankan seeder otomatis jika RUN_SEEDER bernilai true (hanya jika diminta)
if [ "$RUN_SEEDER" = "true" ]; then
    echo "==> Running database seeders..."
    php artisan db:seed --force || echo "==> Peringatan: Seeder gagal atau sudah terisi."
fi

# Jalankan sinkronisasi Firebase users otomatis jika SYNC_FIREBASE_USERS bernilai true
if [ "$SYNC_FIREBASE_USERS" = "true" ]; then
    echo "==> Running Firebase users synchronization..."
    php artisan firebase:sync-users || echo "==> Peringatan: Sinkronisasi Firebase gagal."
fi

# Jalankan web server FrankenPHP pada PORT yang diberikan Railway
export PORT="${PORT:-8080}"
echo "==> FrankenPHP siap melayani permintaan di port :$PORT"
exec frankenphp run --config /etc/caddy/Caddyfile
