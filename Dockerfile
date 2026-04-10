FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application code
COPY . /var/www

# Install PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs --no-interaction --no-progress

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Create supervisord config
RUN echo '[supervisord]'                        > /etc/supervisor/conf.d/app.conf && \
    echo 'nodaemon=true'                       >> /etc/supervisor/conf.d/app.conf && \
    echo ''                                    >> /etc/supervisor/conf.d/app.conf && \
    echo '[program:php-fpm]'                   >> /etc/supervisor/conf.d/app.conf && \
    echo 'command=php-fpm -F'                  >> /etc/supervisor/conf.d/app.conf && \
    echo 'autostart=true'                      >> /etc/supervisor/conf.d/app.conf && \
    echo 'autorestart=true'                    >> /etc/supervisor/conf.d/app.conf && \
    echo ''                                    >> /etc/supervisor/conf.d/app.conf && \
    echo '[program:nginx]'                     >> /etc/supervisor/conf.d/app.conf && \
    echo 'command=nginx -g "daemon off;"'      >> /etc/supervisor/conf.d/app.conf && \
    echo 'autostart=true'                      >> /etc/supervisor/conf.d/app.conf && \
    echo 'autorestart=true'                    >> /etc/supervisor/conf.d/app.conf

# Copy entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
