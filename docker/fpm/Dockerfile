FROM php:8.2-fpm


ARG USER_ID=1000
ARG GROUP_ID=1000


RUN groupadd -g ${GROUP_ID} www && \
    useradd -u ${USER_ID} -g www -m www-user


WORKDIR /var/www/myapp

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    && docker-php-ext-install zip \
    && apt-get clean

RUN docker-php-ext-install pdo pdo_mysql

RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY php.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN chown -R www-user:www /var/www/myapp

USER www-user
