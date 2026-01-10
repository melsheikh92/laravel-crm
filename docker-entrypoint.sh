#!/bin/bash
set -e

echo "Starting Laravel application..."

# Wait for MySQL
echo "Waiting for MySQL..."
until mysql -h mysql -u provensuccess_user -pprovensuccess_password --skip-ssl -e "SELECT 1" &>/dev/null; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
done
echo "MySQL is up!"

# Install dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Set permissions
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "Starting PHP-FPM..."
# Start PHP-FPM in the background
php-fpm -D

echo "Starting Laravel development server..."
# Start Laravel serve in background, but don't fail if it crashes
php artisan serve --host=0.0.0.0 --port=8000 &

# Keep container alive
tail -f /dev/null
