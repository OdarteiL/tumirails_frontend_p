# Backend Dockerfile: Laravel + PHP 8.3 + Composer, runs artisan serve

FROM php:8.3-cli-alpine AS base

RUN set -eux \
    && apk add --no-cache bash git unzip libpq postgresql-dev icu-dev oniguruma-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

# Install Composer (copy from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy overlay files and entrypoint
COPY overlay/ /opt/app-overlay/
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]