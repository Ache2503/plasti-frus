FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libicu-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
        mariadb-client \
        unzip \
        zip \
    && docker-php-ext-install gd intl mbstring pdo_mysql zip \
    && a2enmod rewrite headers \
    && sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader

COPY . .
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/plasti-entrypoint

RUN mkdir -p storage/logs public/uploads \
    && chmod +x /usr/local/bin/plasti-entrypoint \
    && chown -R www-data:www-data storage public/uploads

ENTRYPOINT ["plasti-entrypoint"]
CMD ["apache2-foreground"]
