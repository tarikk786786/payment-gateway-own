FROM php:8.2-apache

# Enable Apache mod_rewrite for routing
RUN a2enmod rewrite

# Set noninteractive to prevent mariadb-server installation from prompting
ENV DEBIAN_FRONTEND=noninteractive

# Install MariaDB server and client, git, unzip for Composer
RUN apt-get update && apt-get install -y \
    mariadb-server mariadb-client git unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install mysqli extension for the database connection
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

# Run Composer Install
RUN cd /var/www/html/auth && composer install --no-dev --optimize-autoloader || true
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader || true

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80 as default for documentation
EXPOSE 80

# Make start script executable and fix Windows line endings
RUN sed -i 's/\r$//' /var/www/html/start.sh && chmod +x /var/www/html/start.sh

# Use the start script to launch Apache
CMD ["/var/www/html/start.sh"]
