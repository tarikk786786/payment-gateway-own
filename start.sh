#!/bin/bash
# Modify Apache configuration to listen on the dynamic Render PORT
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Start Apache in foreground
apache2-foreground
