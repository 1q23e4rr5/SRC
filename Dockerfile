# Dockerfile
FROM php:8.1-apache

# کپی فایل‌ها
COPY *.php *.css *.sql /var/www/html/

# نصب extension های PHP
RUN docker-php-ext-install pdo pdo_mysql

# فعال کردن ماژول Apache
RUN a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]
