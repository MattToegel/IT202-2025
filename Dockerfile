# Use the official PHP 8.2 image with Apache
FROM php:8.5-apache

# 1. Install system dependencies for cURL and PDO
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev

# 2. Install PHP extensions: pdo, pdo_mysql, and curl
RUN docker-php-ext-install pdo pdo_mysql curl

# 3. SET THE WEB ROOT TO public_html
# We define an environment variable for the new path
ENV APACHE_DOCUMENT_ROOT /var/www/html/public_html

# Use 'sed' to update the Apache configuration files to point to the new directory
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 5. Copy your entire repo into the container
# This puts your 'public_html' folder at /var/www/html/public_html
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

EXPOSE 80