#!/bin/sh
set -e

# Wait for the database to actually accept connections — the db container
# process can start before MySQL is ready to serve queries, and app/queue/
# scheduler all race to connect on first boot.
attempt=0
until php -r "new PDO('mysql:host=$DB_HOST;port=$DB_PORT', '$DB_USERNAME', '$DB_PASSWORD');" 2>/dev/null; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge 30 ]; then
        echo "Database not reachable after 60s, giving up." >&2
        exit 1
    fi
    echo "Waiting for the database to be ready..."
    sleep 2
done

# Generate the app key on first boot if the user left it blank in .env —
# never overwrite an existing key (that would invalidate every session and
# any already-encrypted data).
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force --no-interaction
fi

# --isolated uses a cache lock so the app/queue/scheduler containers, which
# all boot from this same entrypoint, don't race to run migrations at once.
php artisan migrate --force --isolated

exec "$@"
