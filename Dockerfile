FROM nginx:alpine

# Install PHP and PHP-FPM
RUN apk add --no-cache \
    php82 \
    php82-fpm \
    php82-opcache \
    php82-session \
    php82-json \
    php82-mbstring \
    php82-curl \
    php82-openssl \
    php82-redis \
    supervisor

# Create PHP-FPM socket directory
RUN mkdir -p /var/run/php

# Copy nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy supervisor configuration
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy PHP-FPM configuration
COPY php-fpm.conf /etc/php82/php-fpm.d/www.conf

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