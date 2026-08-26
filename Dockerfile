# syntax=docker/dockerfile:1

# =========================
# 1. PHP base
# =========================
FROM php:8.3-apache AS app

WORKDIR /var/www/html

# =========================
# 2. System dependencies
# =========================
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        bcmath \
        intl \
        zip \
        opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# =========================
# 3. Composer
# =========================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =========================
# 4. Node.js / npm
# =========================
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm --version \
    && node --version

# =========================
# 5. Copy dependency files
# =========================
COPY composer.json composer.lock ./
COPY package.json package-lock.json* ./

# =========================
# 6. Install PHP dependencies
# =========================
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# =========================
# 7. Install frontend dependencies
# =========================
RUN npm ci

# =========================
# 8. Copy application
# =========================
COPY . .

# =========================
# 9. Laravel application setup
# =========================
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

RUN chown -R www-data:www-data \
    storage \
    bootstrap/cache

# =========================
# 10. Build React / Vite
# =========================
RUN npm run build

# =========================
# 11. Apache → Laravel public/
# =========================
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

# Laravel front-controller rewrite
RUN printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# =========================
# 12. PHP production settings
# =========================
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.revalidate_freq=0'; \
} > /usr/local/etc/php/conf.d/production.ini

# =========================
# 13. Render
# =========================
EXPOSE 80

CMD ["apache2-foreground"]