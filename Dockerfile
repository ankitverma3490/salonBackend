FROM php:8.1-apache-bullseye

# Configure Apache: Cleanup MPMs and ensure only prefork is enabled
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_* \
    && a2enmod mpm_prefork \
    && docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

EXPOSE 80
