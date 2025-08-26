#!/bin/bash
set -e

# Wait for MySQL to be ready
until nc -z -v -w30 laravel_mysql 3306
do
  echo "Waiting for MySQL to start..."
  sleep 2
done

echo "MySQL started successfully!"

# Run Laravel setup
php artisan config:clear
php artisan cache:clear
php artisan key:generate --force
php artisan migrate --force || true

# Start Laravel dev server
php artisan serve --host=0.0.0.0 --port=8000
