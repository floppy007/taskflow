FROM php:8.2-apache

# Apache-Module aktivieren
RUN a2enmod deflate expires headers rewrite

# PHP-Konfiguration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Anwendung kopieren
COPY . /var/www/html/

# install.php entfernen (Setup erfolgt über Entrypoint)
RUN rm -f /var/www/html/install.php

# Entrypoint-Script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
