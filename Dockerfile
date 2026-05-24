FROM php:8.2-apache

# Enable Apache mod_rewrite and performance modules
RUN a2enmod rewrite headers expires deflate

# Set noninteractive to prevent mariadb-server installation from prompting
ENV DEBIAN_FRONTEND=noninteractive

# Install MariaDB server and client, git, unzip for Composer
RUN apt-get update && apt-get install -y \
    mariadb-server mariadb-client git unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install mysqli, pdo, pdo_mysql and OPcache for PHP performance
RUN docker-php-ext-install mysqli pdo pdo_mysql opcache

# Configure PHP OPcache for maximum performance
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.revalidate_freq=60'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.huge_code_pages=1'; \
} > /usr/local/etc/php/conf.d/opcache-recommended.ini

# Configure PHP performance settings
RUN { \
    echo 'memory_limit=256M'; \
    echo 'max_execution_time=60'; \
    echo 'output_buffering=On'; \
    echo 'zlib.output_compression=On'; \
    echo 'zlib.output_compression_level=6'; \
    echo 'realpath_cache_size=4096K'; \
    echo 'realpath_cache_ttl=600'; \
    echo 'expose_php=Off'; \
    echo 'session.gc_maxlifetime=3600'; \
    echo 'session.cookie_httponly=1'; \
    echo 'session.cookie_secure=0'; \
} > /usr/local/etc/php/conf.d/performance.ini

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
