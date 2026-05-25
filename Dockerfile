# Avoid interactive prompts
ENV DEBIAN_FRONTEND=noninteractive

# Install Apache and PHP together cleanly
RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    php-mysqli \
    libapache2-mod-php \
    && apt-get clean

# Remove default Apache page
RUN rm -f /var/www/html/index.html

# Copy your files
COPY . /var/www/html/

# Set permissions
RUN chmod -R 755 /var/www/html

EXPOSE 80

# Start Apache
CMD ["apachectl", "-D", "FOREGROUND"]