FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Copy all project files to Apache document root
COPY . .

# Expose port 10000 (as per task requirement)
EXPOSE 10000

# Start Apache in foreground
CMD ["apache2-foreground"]
