# Image de développement Dotclear : Apache + PHP + Xdebug + Composer
ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-apache

ENV DEBIAN_FRONTEND=noninteractive

# Dépendances système et extensions PHP requises par Dotclear (gd, intl, mysqli, pgsql, zip)
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl git unzip gettext-base default-mysql-client \
        libicu-dev libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev \
        libzip-dev libxml2-dev libpq-dev libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd intl mysqli pgsql pdo_pgsql zip opcache \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Composer (outils qualité : phpstan, php-cs-fixer)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Configuration PHP et Xdebug
COPY php-dev.ini /usr/local/etc/php/conf.d/zz-dev.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Fichier de configuration Dotclear piloté par variables d'environnement
COPY dotclear-config.php /opt/dotclear/config.php
COPY entrypoint.sh /usr/local/bin/dotclear-entrypoint
RUN chmod +x /usr/local/bin/dotclear-entrypoint

# Utile pour `git` dans le devcontainer (le dépôt est monté sous /workspace)
RUN git config --system --add safe.directory /workspace \
    && git config --system --add safe.directory /var/www/html

WORKDIR /var/www/html
ENTRYPOINT ["dotclear-entrypoint"]
CMD ["apache2-foreground"]
