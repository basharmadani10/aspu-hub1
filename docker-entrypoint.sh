#!/bin/bash
set -e

php artisan config:clear
php artisan migrate --force

# Always remove public/storage regardless of its type
echo "Cleaning public/storage..."
rm -rf public/storage

php artisan storage:link

exec "$@"
