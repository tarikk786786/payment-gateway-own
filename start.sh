#!/bin/bash
# Dump Render environment variables to .env so PHP can read them
printenv > /var/www/html/.env

# Ensure PORT has a fallback
PORT=${PORT:-10000}

# Modify Apache configuration to listen on the dynamic Render PORT
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Start MySQL Service
service mariadb start
sleep 3

# Create database, user, and import schema
mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`Tarik7-353033376eab\`;"
mysql -u root -e "CREATE USER IF NOT EXISTS 'Tarik7-353033376eab'@'localhost' IDENTIFIED BY 'Tarik@786';"
mysql -u root -e "GRANT ALL PRIVILEGES ON \`Tarik7-353033376eab\`.* TO 'Tarik7-353033376eab'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"
mysql -u root \`Tarik7-353033376eab\` < /var/www/html/u740980038_smmm.sql

# Start Apache in foreground
apache2-foreground
