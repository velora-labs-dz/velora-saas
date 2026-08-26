#!/bin/sh
set -e

echo "Running Laravel migrations..."

php artisan migrate --force

echo "Starting Laravel..."

exec apache2-foreground