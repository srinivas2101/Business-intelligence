FROM php:8.2-apache

RUN docker-php-ext-install mysqli \
    && a2enmod headers rewrite

# backend/api/*.php use "require_once '../config/...'" so config must sit
# next to api/ inside the web root, matching the original folder layout.
COPY backend/api        /var/www/html/api
COPY backend/config     /var/www/html/config
COPY backend/templates  /var/www/html/templates

# Keep the config folder from being fetched directly (defense in depth —
# the .php files just define constants and print nothing, but no reason to
# expose them).
RUN printf "Require all denied\n" > /var/www/html/config/.htaccess

EXPOSE 80