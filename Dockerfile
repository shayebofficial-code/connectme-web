# ============================================================
# ConnectMe - Dockerfile for Render.com & Container Deployment
# ============================================================

FROM php:8.1-apache

# Install PDO MySQL extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy application source code
COPY . /var/www/html/

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Ensure uploads directory structure exists & set permissions
RUN mkdir -p /var/www/html/uploads/profiles \
 && chown -R www-data:www-data /var/www/html/ \
 && chmod -R 755 /var/www/html/uploads

EXPOSE 80
