#!/bin/bash
set -e  # Exit immediately if any command fails

echo "Starting application..."

# Wait for database to be ready (optional but recommended)
# Note: This requires `db:monitor` to be configured. 
# If it fails, you can comment it out for now.
echo "Waiting for database connection..."
while ! php artisan db:monitor > /dev/null 2>&1; do
    echo "Database not ready, waiting..."
    sleep 2
done
echo "Database is ready."

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations safely
if [[ "${RAILWAY_ENVIRONMENT}" == "production" ]]; then
    echo "Running production migrations..."
    php artisan migrate --force
else
    echo "Running development migrations..."
    php artisan migrate
fi

# Create storage link
php artisan storage:link

echo "Application setup complete. Starting services..."

# Start services
php-fpm8.2 -D
nginx -g 'daemon off;'