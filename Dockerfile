FROM php:8.2-apache

# Enable Apache mod_rewrite for routing
RUN a2enmod rewrite

# Install mysqli extension for the database connection
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port (Render sets this dynamically, but defaults to 10000 locally if we use it)
ENV PORT=80

# Update Apache configuration to listen on the PORT environment variable
RUN sed -s -i -e "s/80/\$\{PORT\}/" /etc/apache2/ports.conf /etc/apache2/sites-available/*.conf
