#!/bin/bash
set -e  # Exit immediately if any command fails

echo "Starting application..."

# Dynamically set the port NGINX listens on
sed -i "s/listen 80;/listen ${PORT};/" /etc/nginx/sites-available/default
echo "NGINX configured to listen on port ${PORT}"

# --- THIS IS THE NEW FIX ---
# Publish assets for Filament and Livewire
echo "Publishing assets..."
php artisan vendor:publish --tag=filament-assets --force
php artisan livewire:publish --assets
# --- END OF FIX ---

# Cache for production
echo "Caching configuration..."
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
php-fpm8.3 -D
nginx -g 'daemon off;'