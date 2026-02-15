FROM php:8.1-apache

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork \
    && docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

EXPOSE 80
