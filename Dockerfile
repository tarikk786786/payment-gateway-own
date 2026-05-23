FROM php:8.2-apache

# Enable Apache mod_rewrite for routing
RUN a2enmod rewrite

# Install Apache, PHP, and required extensions including MariaDB server
RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    libapache2-mod-php \
    php-mysql \
    php-curl \
    mariadb-server \
    && rm -rf /var/lib/apt/lists/*

# Install mysqli extension for the database connection
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 as default for documentation
EXPOSE 80

# Make start script executable and fix Windows line endings
RUN sed -i 's/\r$//' /var/www/html/start.sh && chmod +x /var/www/html/start.sh

# Use the start script to launch Apache
CMD ["/var/www/html/start.sh"]
