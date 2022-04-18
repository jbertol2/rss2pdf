FROM php:8.0.14-fpm-bullseye

RUN apt-get update -y && apt-get install -y sendmail libpng-dev

RUN apt-get update && \
    apt-get install -y \
        zlib1g-dev
        
RUN docker-php-ext-install gd