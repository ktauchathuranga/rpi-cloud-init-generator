FROM php:8.2-fpm-alpine

# Install nginx, supervisor, and dependencies for zip
RUN apk add --no-cache \
    nginx \
    supervisor \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/cache/apk/*

# Install PHP extensions including zip
RUN docker-php-ext-install opcache zip

# Configure PHP
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini

# Copy nginx config
COPY nginx.conf /etc/nginx/http.d/default.conf

# Create supervisord config
RUN mkdir -p /etc/supervisor.d
RUN echo -e "[supervisord]\nnodaemon=true\nlogfile=/dev/null\nlogfile_maxbytes=0\nuser=root\n\n[program:nginx]\ncommand=nginx -g 'daemon off;'\nautostart=true\nautorestart=true\nstdout_logfile=/dev/stdout\nstdout_logfile_maxbytes=0\nstderr_logfile=/dev/stderr\nstderr_logfile_maxbytes=0\n\n[program:php-fpm]\ncommand=php-fpm -F\nautostart=true\nautorestart=true\nstdout_logfile=/dev/stdout\nstdout_logfile_maxbytes=0\nstderr_logfile=/dev/stderr\nstderr_logfile_maxbytes=0" > /etc/supervisor.d/supervisord.ini

# Set working directory and fix permissions
WORKDIR /var/www/html

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html \
    && chown -R nginx:nginx /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port
EXPOSE 80

# Start supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor.d/supervisord.ini"]