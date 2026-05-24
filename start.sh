#!/bin/bash
# Dump Render environment variables to .env so PHP can read them
printenv > /var/www/html/.env

# Ensure PORT has a fallback
PORT=${PORT:-10000}

# Modify Apache configuration to listen on the dynamic Render PORT
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Write MariaDB performance tuning config
cat > /etc/mysql/conf.d/performance.cnf << 'EOF'
[mysqld]
# Connection & thread settings
max_connections=100
thread_cache_size=8

# InnoDB performance
innodb_buffer_pool_size=128M
innodb_log_file_size=32M
innodb_flush_method=O_DIRECT
innodb_flush_log_at_trx_commit=2

# Query cache
query_cache_type=1
query_cache_size=32M
query_cache_limit=2M

# General tuning
key_buffer_size=32M
sort_buffer_size=2M
read_buffer_size=2M
join_buffer_size=2M
tmp_table_size=64M
max_heap_table_size=64M

# Skip unnecessary lookups
skip-name-resolve=1
EOF

# Ensure MySQL socket directory exists with correct permissions
mkdir -p /var/run/mysqld
chown -R mysql:mysql /var/run/mysqld

# Start MySQL Service
/etc/init.d/mariadb start || /etc/init.d/mysql start

# Wait for MariaDB to be ready
echo "Waiting for MariaDB to start..."
while ! mysqladmin ping -h"localhost" --silent; do
    sleep 1
done
echo "MariaDB started successfully!"

# Create database, user, and import schema
mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`Tarik7-353033376eab\`;"
mysql -u root -e "CREATE USER IF NOT EXISTS 'Tarik7-353033376eab'@'localhost' IDENTIFIED BY 'Tarik@786';"
mysql -u root -e "GRANT ALL PRIVILEGES ON \`Tarik7-353033376eab\`.* TO 'Tarik7-353033376eab'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"
mysql -u root "Tarik7-353033376eab" < /var/www/html/auth/database.sql

# Start Apache in foreground
apache2-foreground
