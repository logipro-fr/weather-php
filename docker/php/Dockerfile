FROM php:8.2.11-fpm

# Définir le fuseau horaire
ENV TZ=Europe/Paris
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Installation des dépendances système
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Installation de l'extension pecl pcov
RUN pecl install pcov \
    && docker-php-ext-enable pcov

RUN docker-php-ext-configure zip \
    && docker-php-ext-install -j$(nproc) zip \
    && docker-php-ext-configure exif \
    && docker-php-ext-install -j$(nproc) exif \
    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && docker-php-ext-configure mysqli \
    && docker-php-ext-install -j$(nproc) mysqli

COPY ./php/php.ini $PHP_INI_DIR/php.ini

EXPOSE 9000
