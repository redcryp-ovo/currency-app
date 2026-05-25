FROM php:8.1-apache

# Fix the MPM conflict
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true
RUN a2enmod mpm_prefork

# Install MySQL extension
RUN docker-php-ext-install mysqli

# Copy your files
COPY . /var/www/html/

# Set permissions
RUN chmod -R 755 /var/www/html

EXPOSE 80

# Start Apache correctly
CMD ["apache2-foreground"]