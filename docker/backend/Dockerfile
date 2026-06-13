FROM php:8.3-fpm-alpine

# System libraries and PHP extensions required by Laravel + PostgreSQL.
RUN apk add --no-cache postgresql-dev git unzip \
    && docker-php-ext-install pdo pdo_pgsql bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies first to leverage Docker layer caching. The source is
# bind-mounted in development (see docker-compose.yml), which reuses the host
# vendor directory.
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-interaction --no-scripts --prefer-dist --no-progress

COPY backend/ .

EXPOSE 9000

CMD ["php-fpm"]
