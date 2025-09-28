# Use official PHP with Apache
FROM php:8.1-apache

# Install mysqli and enable extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Enable Apache rewrite module (useful for clean URLs)
RUN a2enmod rewrite

# Copy project files into container (context is already ./src)
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Set permissions (so Apache can read/write)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 80
EXPOSE 80
