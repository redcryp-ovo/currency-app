FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libapache2-mod-php

RUN phpdismod -v ALL -s ALL mpm_event || true
RUN a2dismod mpm_event || true
RUN a2enmod mpm_prefork || true
RUN docker-php-ext-install mysqli
RUN a2enmod rewrite

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80