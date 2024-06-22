FROM php:8.2-apache

# Установите необходимые расширения PHP
RUN docker-php-ext-install mysqli

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Создайте каталог сессий и установите права доступа
RUN mkdir -p /var/lib/php/sessions && chown -R www-data:www-data /var/lib/php/sessions

# Скопируйте файл конфигурации php.ini
COPY php.ini /usr/local/etc/php/
