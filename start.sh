#!/bin/bash
set -e  # Exit immediately if any command fails

# --- THIS IS THE FIX ---
# Wait 5 seconds for Railway to inject all new env variables
echo "Waiting for environment variables..."
sleep 5
# Clear any old cached config that had the wrong password
php artisan config:clear
# --- END OF FIX ---

echo "Starting application..."

# Dynamically set the port NGINX listens on
sed -i "s/listen 80;/listen ${PORT};/" /etc/nginx/sites-available/default
echo "NGINX configured to listen on port ${PORT}"

# Publish assets for Filament and Livewire
echo "Publishing assets..."
php artisan vendor:publish --tag=filament-assets --force
php artisan livewire:publish --assets

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