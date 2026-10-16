#!/usr/bin/env bash
#
# Container entrypoint: makes the API usable with a single `docker compose up`.
# Every step is idempotent, so restarting the container is always safe.
#
set -euo pipefail

cd /var/www/html

log() { printf '\033[0;36m[entrypoint]\033[0m %s\n' "$1"; }

# ---------------------------------------------------------------------------
# Environment file and application key
# ---------------------------------------------------------------------------
if [ ! -f .env ]; then
    log 'Creating .env from .env.example'
    cp .env.example .env
fi

if [ -z "${APP_KEY:-}" ]; then
    if ! grep -qE '^APP_KEY=.+' .env; then
        log 'Generating the application key'
        php artisan key:generate --force --no-interaction
    fi

    # An APP_KEY present but empty in the container environment would shadow the
    # one stored in .env, because Dotenv never overrides real environment
    # variables. Exporting it keeps both sources in sync for php-fpm.
    APP_KEY="$(grep -E '^APP_KEY=' .env | head -n1 | cut -d'=' -f2-)"
    export APP_KEY
fi

# ---------------------------------------------------------------------------
# Dependencies (useful when the source is bind mounted over the image)
# ---------------------------------------------------------------------------
if [ ! -f vendor/autoload.php ]; then
    log 'Installing composer dependencies'
    composer install --prefer-dist --no-progress --no-interaction
fi

# ---------------------------------------------------------------------------
# Wait for the database
# ---------------------------------------------------------------------------
if [ "${DB_CONNECTION:-mariadb}" != "sqlite" ]; then
    log "Waiting for the database at ${DB_HOST:-database}:${DB_PORT:-3306}"
    attempts=0
    until php -r '
        $host = getenv("DB_HOST") ?: "database";
        $port = (int) (getenv("DB_PORT") ?: 3306);
        $socket = @fsockopen($host, $port, $errno, $error, 1);
        exit($socket === false ? 1 : 0);
    '; do
        attempts=$((attempts + 1))
        if [ "${attempts}" -ge 60 ]; then
            log 'The database is not reachable, giving up.'
            exit 1
        fi
        sleep 1
    done
    log 'Database is up'
fi

# ---------------------------------------------------------------------------
# Schema, OAuth2 keys/clients and demo data
# ---------------------------------------------------------------------------
log 'Running migrations'
php artisan migrate --force --no-interaction

if [ ! -f storage/oauth-private.key ]; then
    log 'Generating the OAuth2 signing keys'
    php artisan passport:keys --force --no-interaction
fi

log 'Ensuring the OAuth2 clients exist'
php artisan oauth:ensure-clients --no-interaction

log 'Seeding the demo user'
php artisan db:seed --force --no-interaction

# A cached config would ignore the environment of the container.
php artisan config:clear --no-interaction >/dev/null
php artisan route:clear --no-interaction >/dev/null

log 'Ready - the API is available through the nginx service'

exec "$@"
