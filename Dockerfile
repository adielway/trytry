# Use official PHP with Apache
FROM php:8.2-apache

# Install MySQL extension for PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql


# Copy all project files into Apache web root
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
