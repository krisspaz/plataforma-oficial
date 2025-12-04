# Backend Dockerfile

FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    intl \
    zip \
    opcache \
    bcmath

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Development stage
FROM base AS development

RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Production stage
FROM base AS production

# Copy application
COPY --chown=www-data:www-data . .

# Install dependencies (no dev)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Warm up cache
RUN php bin/console cache:warmup --env=prod

# Set permissions
RUN chown -R www-data:www-data var/

# PHP production config
COPY docker/php/php.prod.ini /usr/local/etc/php/conf.d/php.ini

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
