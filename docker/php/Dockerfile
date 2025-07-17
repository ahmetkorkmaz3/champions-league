FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    curl \
    npm \
    && docker-php-ext-install pdo pdo_pgsql bcmath

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Create www-data user if it doesn't exist and set permissions
RUN groupadd -g 1000 www-data || true && \
    useradd -u 1000 -ms /bin/bash -g www-data www-data || true

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

USER www-data
