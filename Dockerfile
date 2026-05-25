FROM php:8.1-apache
RUN docker-php-ext-install mysqli
RUN a2dismod mpm_event mpm_event mpm_worker && a2enmod mpm_prefork
COPY . /var/www/html/
EXPOSE 80
