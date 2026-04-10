#!/bin/sh
set -e

cd /var/www

# Generate .env from environment variables
if [ ! -f .env ]; then
    echo "APP_NAME=${APP_NAME:-Laravel}" > .env
    echo "APP_ENV=${APP_ENV:-production}" >> .env
    echo "APP_KEY=${APP_KEY}" >> .env
    echo "APP_DEBUG=${APP_DEBUG:-false}" >> .env
    echo "APP_URL=${APP_URL:-http://localhost}" >> .env
    echo "LOG_CHANNEL=${LOG_CHANNEL:-stderr}" >> .env
    echo "DB_CONNECTION=${DB_CONNECTION:-mysql}" >> .env
    echo "DB_HOST=${DB_HOST:-127.0.0.1}" >> .env
    echo "DB_PORT=${DB_PORT:-3306}" >> .env
    echo "DB_DATABASE=${DB_DATABASE:-laravel}" >> .env
    echo "DB_USERNAME=${DB_USERNAME:-root}" >> .env
    echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
    echo "SESSION_DRIVER=${SESSION_DRIVER:-file}" >> .env
    echo "CACHE_STORE=${CACHE_STORE:-file}" >> .env
    echo "QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}" >> .env
    echo "FILESYSTEM_DISK=local" >> .env
    echo "VITE_APP_NAME=${APP_NAME:-Laravel}" >> .env
fi

# Make sure storage directories exist
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 777 storage bootstrap/cache

# Clear caches
php artisan config:clear 2>&1 || true
php artisan view:clear 2>&1 || true

echo "=== CFARM HR System Starting ==="
echo "APP_URL: ${APP_URL}"
echo "DB_HOST: ${DB_HOST}"
echo "================================"

# Start Apache in foreground (standard Docker way)
exec apache2-foreground
