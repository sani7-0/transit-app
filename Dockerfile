FROM php:8.1-apache

# Install MySQL extensions if your PHP uses MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache rewrite module (if you need pretty URLs)
RUN a2enmod rewrite

# Copy all your project files into Apache’s web root
COPY . /var/www/html/

# Expose port 80 for HTTP
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
