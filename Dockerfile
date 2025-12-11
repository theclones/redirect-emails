FROM nginx:alpine

# Install PHP and PHP-FPM
RUN apk add --no-cache \
    php83 \
    php83-fpm \
    php83-opcache \
    php83-session \
    php83-mbstring \
    php83-curl \
    php83-openssl \
    php83-redis \
    supervisor

# Create PHP-FPM socket directory
RUN mkdir -p /var/run/php

# Copy nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy PHP-FPM configuration
COPY php-fpm.conf /etc/php83/php-fpm.d/www.conf

# Create web directory
RUN mkdir -p /var/www/html
COPY . /var/www/html/

# Set permissions
RUN chown -R nginx:nginx /var/www/html
RUN chmod -R 755 /var/www/html

# Create cache directory for campaign decisions
RUN mkdir -p /var/cache/campaign && chown nginx:nginx /var/cache/campaign

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]