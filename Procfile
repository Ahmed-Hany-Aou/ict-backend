web: echo "Starting on PORT=$PORT" && php artisan migrate --force && php artisan config:clear && php artisan config:cache && php -S 0.0.0.0:$PORT -t public
