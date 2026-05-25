FROM php:8.1-apache
RUN apt-get update && apt-get install -y \
         && docker-php-ext-install mysqli
RUN a2dismod mpm_event || true
RUN a2dismod mpm_worker ||true
RUN a2enmod mpm_prefork ||true
COPY . /var/www/html/
EXPOSE 80
CMD ["apache2-foreground"] 
