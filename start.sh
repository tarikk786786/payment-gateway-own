#!/bin/bash
# Dump Render environment variables to .env so PHP can read them
printenv > /var/www/html/.env

# Ensure PORT has a fallback
PORT=${PORT:-10000}

# Modify Apache configuration to listen on the dynamic Render PORT
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Start Apache in foreground
apache2-foreground
