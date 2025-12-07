#!/bin/bash

# Wait for database to be ready
echo "Waiting for database connection..."
max_attempts=30
attempt=0

until php artisan migrate:status --database=pgsql >/dev/null 2>&1 || [ $attempt -eq $max_attempts ]; do
    attempt=$((attempt + 1))
    echo "Waiting for database... (attempt $attempt/$max_attempts)"
    sleep 2
done

if [ $attempt -eq $max_attempts ]; then
    echo "Failed to connect to database after $max_attempts attempts"
    exit 1
fi

echo "Database connection established!"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

echo "Migrations completed successfully!"

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
