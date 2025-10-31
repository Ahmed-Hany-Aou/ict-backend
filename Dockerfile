# Build stage
FROM composer:2.6 as build

WORKDIR /app
COPY . .
# --prefer-dist is faster, --no-dev saves space
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Production stage
FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies (NGINX and PHP)
RUN apt-get update && apt-get install -y \
    nginx \
    php8.2-fpm \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure nginx & PHP-FPM
COPY nginx.conf /etc/nginx/sites-available/default
RUN sed -i 's/;clear_env = no/clear_env = no/' /etc/php/8.2/fpm/pool.d/www.conf

# Copy application files from the 'build' stage
COPY --from=build /app /var/www/html
WORKDIR /var/www/html

# Set correct permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy the startup script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

# Run the startup script
CMD ["/usr/local/bin/start.sh"]