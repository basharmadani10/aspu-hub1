#!/bin/bash
set -e
php artisan key:generate || true
php artisan storage:link || true
php artisan migrate --force || true
exec "$@"
