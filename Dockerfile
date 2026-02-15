FROM php:8.2-apache

# Remove conflicting MPMs manually
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load \
    && rm -f /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.conf \
    && a2enmod mpm_prefork \
    && docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

EXPOSE 80
