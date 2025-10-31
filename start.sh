#!/bin/bash
set -e  # Exit immediately if any command fails

echo "Starting application..."

# Dynamically set the port NGINX listens on
sed -i "s/listen 80;/listen ${PORT};/" /etc/nginx/sites-available/default
echo "NGINX configured to listen on port ${PORT}"

# Publish assets for Filament and Livewire
echo "Publishing assets..."
php artisan vendor:publish --tag=filament-assets --force
php artisan livewire:publish --assets

# --- THIS IS THE FIX ---
# CLEAR all caches. DO NOT create new ones.
# This forces Laravel to read variables from the live environment.
echo "Clearing all caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
# --- END OF FIX ---

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