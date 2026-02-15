# Start from a PHP 8.1 Apache base image
FROM php:8.1-apache-bullseye

# Copy custom entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Install necessary PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules and configure directory permissions
RUN a2enmod rewrite headers && \
    echo '<Directory /var/www/html>' >> /etc/apache2/apache2.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '    Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

# Copy application source code
COPY . /var/www/html/

# Use the custom entrypoint script to start Apache
ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 80
