FROM php:8.0-apache


RUN apt-get update && apt-get install -y \
    git \
    zip \
    curl \
    sudo \
    unzip \
    libzip-dev \
    libicu-dev \
    libbz2-dev \
    libpng-dev \
    libjpeg-dev \
    libmcrypt-dev \
    libreadline-dev \
    libfreetype6-dev \
    g++

RUN  docker-php-ext-install mysqli pdo pdo_mysql



# Install Composer
# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer




COPY . /var/www/html

USER 1000:1000