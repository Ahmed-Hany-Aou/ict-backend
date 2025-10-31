# Build stage
FROM composer:2.6 as build

# Install dependencies for ext-intl (using Alpine's 'apk' manager)
RUN apk add --no-cache icu-dev \
    && docker-php-ext-install intl \
    && rm -rf /var/cache/apk/*

WORKDIR /app
COPY . .
# --prefer-dist is faster, --no-dev saves space
RUN composer install --no-dev --optimize-autoloader --prefer-dist

# Production stage
FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

# Install dependencies and the ondrej/php PPA
RUN apt-get update && apt-get install -y software-properties-common \
    && add-apt-repository ppa:ondrej/php -y \
    && apt-get update \
    # Now install NGINX and PHP 8.3
    && apt-get install -y \
        nginx \
        php8.3-fpm \
        php8.3-mysql \
        php8.3-mbstring \
        php8.3-xml \
        php8.3-curl \
        php8.3-zip \
        php8.3-gd \
        php8.3-intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure nginx & PHP-FPM
COPY nginx.conf /etc/nginx/sites-available/default
# Update path for PHP 8.3
RUN sed -i 's/;clear_env = no/clear_env = no/' /etc/php/8.3/fpm/pool.d/www.conf

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