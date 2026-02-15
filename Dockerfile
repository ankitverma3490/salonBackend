# Start from a PHP 8.1 Apache base image
FROM php:8.1-apache-bullseye

# Install necessary PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite headers

# Configure AllowOverride All for .htaccess
# We append to apache2.conf to override previous settings for /var/www/html
RUN echo '<Directory /var/www/html>' >> /etc/apache2/apache2.conf && \
    echo '    Options Indexes FollowSymLinks' >> /etc/apache2/apache2.conf && \
    echo '    AllowOverride All' >> /etc/apache2/apache2.conf && \
    echo '    Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

# Copy application source code
COPY . /var/www/html/

# Clean up MPMs and start Apache at runtime (inlined for reliability)
# We remove any conflicting MPMs and ensure prefork is enabled before starting
CMD ["/bin/sh", "-c", "rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf && a2enmod mpm_prefork && echo 'ServerName localhost' >> /etc/apache2/apache2.conf && exec apache2-foreground"]

EXPOSE 80
