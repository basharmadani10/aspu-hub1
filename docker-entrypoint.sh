#!/bin/bash

set -e  # Exit script on error

# Copy .env.example to .env if .env does not exist
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/tmp/.env.example /var/www/html/.env
fi

# Copy application files to document root
cp -R /var/www/tmp/. /var/www/html/

# Ensure correct permissions
chown -R www-data:www-data /var/www/html


# Generate application key
cd /var/www/html
php artisan key:generate

# Run database migrations
php artisan migrate --force || true  # Allow migration errors without stopping container

# Start Apache
exec apache2-foreground
