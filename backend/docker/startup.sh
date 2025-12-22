#!/bin/bash

# Wait for database to be ready
echo "Waiting for database connection..."
max_attempts=30
attempt=0

until php artisan db:show --database=pgsql >/dev/null 2>&1 || [ $attempt -eq $max_attempts ]; do
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

# Optionally seed demo data on startup when SEED_ON_STARTUP=true
if [ "${SEED_ON_STARTUP:-false}" = "true" ]; then
    SEEDED_FILE="$(pwd)/storage/app/.seeded"
    if [ -f "$SEEDED_FILE" ]; then
        echo "Database already seeded (found .seeded). Skipping demo seeding."
    else
        echo "Seeding demo data on startup..."
        php artisan db:seed --class=DatabaseSeeder --force
        php artisan app:seed-demo --force
        # create flag file to avoid reseeding
        mkdir -p storage/app
        touch "$SEEDED_FILE"
        echo "Demo data seeded."
    fi
fi

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
