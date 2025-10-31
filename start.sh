#!/bin/bash
set -e  # Exit immediately if any command fails

echo "Starting application..."

# --- THIS IS THE NEW FIX ---
# Dynamically set the port NGINX listens on from Railway's $PORT variable
sed -i "s/listen 80;/listen ${PORT};/" /etc/nginx/sites-available/default
echo "NGINX configured to listen on port ${PORT}"
# --- END OF FIX ---

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
php-fpm8.3 -D
nginx -g 'daemon off;'