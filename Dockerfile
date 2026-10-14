# syntax=docker/dockerfile:1

###############################################################################
# Challenge PHP Hexagonal - GIFs API
#
# PHP 8.3 + FPM image with everything Laravel 12 and Passport need.
# Used by both the `app` service of docker-compose.yml and by CI.
###############################################################################
FROM php:8.3-fpm-bookworm

ARG UID=1000
ARG GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_MEMORY_LIMIT=-1

# ---------------------------------------------------------------------------
# System dependencies and PHP extensions
# ---------------------------------------------------------------------------
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libsodium-dev \
        default-mysql-client \
    ; \
    docker-php-ext-configure intl; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        opcache \
        pdo_mysql \
        zip \
    ; \
    # sodium ships enabled in the official images; compile it only if missing
    # (Passport signs its JWTs through lcobucci/jwt, which requires it).
    php -m | grep -qi '^sodium$' || docker-php-ext-install -j"$(nproc)" sodium; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini

# ---------------------------------------------------------------------------
# Non root user matching the host, so the bind mounted files stay writable
# ---------------------------------------------------------------------------
RUN set -eux; \
    groupmod -o -g "${GID}" www-data; \
    usermod -o -u "${UID}" -g "${GID}" www-data

WORKDIR /var/www/html

# Dependencies first: this layer is cached while composer.json does not change.
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-progress

COPY . .

RUN set -eux; \
    composer dump-autoload --optimize; \
    mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 9000

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
