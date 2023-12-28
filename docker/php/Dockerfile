ARG ENV
ARG TIMEZONE

FROM php:8.0.10-fpm-buster
RUN apt-get update
RUN apt-get install -y \
    apt-utils \
    ca-certificates \
    apt-transport-https \
    software-properties-common \
    zlib1g-dev \
    libxml2-dev \
    libzip-dev \
    unzip \
    postgresql-server-dev-11

COPY config/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

RUN docker-php-ext-install \
    intl \
    pdo_pgsql \
    zip \
    opcache

ARG ENV

RUN if [ "$ENV" = "dev" ] ; then \
        pecl install xdebug-3.0.4 && \
        docker-php-ext-enable xdebug  \
    ; fi

COPY config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY config/docker-php-memlimit.ini /usr/local/etc/php/conf.d/docker-php-memlimit.ini

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

ARG TIMEZONE

RUN ln -snf /usr/share/zoneinfo/$TIMEZONE /etc/localtime && echo $TIMEZONE > /etc/timezone

WORKDIR /var/www/app

RUN usermod -u 1000 www-data