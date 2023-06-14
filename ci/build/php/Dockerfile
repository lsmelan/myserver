# Use the official PHP image as the base
FROM php:8.2-apache

# Install Apache and other dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    libicu-dev \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite vhost_alias

# Install PHP extensions required by Symfony
RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo_pgsql opcache zip intl

# Install composer and Symfony CLI
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -sS https://get.symfony.com/cli/installer | bash \
    && mv $HOME/.symfony5/bin/symfony /usr/local/bin/symfony

# Copy Symfony project files to the Apache document root
COPY . /var/www/html

# Set the working directory
WORKDIR /var/www/html