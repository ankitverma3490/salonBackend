FROM php:8.2-apache

COPY . /var/www/html/

RUN a2dismod mpm_event || true \
    && a2dismod mpm_worker || true \
    && a2enmod mpm_prefork \
    && docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 80
