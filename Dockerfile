FROM php:8.2-apache

# Set environment variables for non-interactive installs
ENV DEBIAN_FRONTEND=noninteractive

# Install common dependencies for Laravel and PHP extensions
# Remove libmcrypt-dev if on PHP 7.2+
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    curl \
    sudo \
    libicu-dev \
    libbz2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    nano \
    g++ \
    --no-install-recommends && rm -rf /var/lib/apt/lists/*


ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite headers


RUN docker-php-ext-install -j$(nproc) \
    bz2 \
    intl \
    iconv \
    bcmath \
    opcache \
    calendar \
    pdo_mysql \
    gd # gd for image manipulation with libpng-dev, libjpeg-dev, libfreetype6-dev


RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'upload_max_filesize = 64M'; \
    echo 'post_max_size = 64M'; \
} | tee /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini > /dev/null \
&& { \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
} | tee /usr/local/etc/php/conf.d/custom.ini > /dev/null

# Copy Composer from its official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory inside the container
WORKDIR /var/www/html


COPY . .


RUN composer require predis/predis

RUN composer install --no-dev --optimize-autoloader --no-interaction




RUN chown -R www-data:www-data public storage bootstrap/cache
RUN chmod -R 775 public storage bootstrap/cache

EXPOSE 80

# Direct logs to stderr for Kubernetes logging
ENV LOG_CHANNEL=stderr


COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]


CMD ["apache2-foreground"]



