FROM php:8.1-apache

RUN a2dismod mpm_event || true && \
    a2enmod mpm_prefork || true && \
    docker-php-ext-install mysqli && \
    a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80