FROM php:8.1-apache

# نصب dependency ها
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip unzip git curl

# پیکربندی PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install pdo pdo_mysql mysqli gd mbstring xml zip

# فعال کردن mod_rewrite
RUN a2enmod rewrite

# کپی فایل‌ها
COPY . /var/www/html/

# تنظیم مجوزها
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod 777 /var/www/html

# ایجاد فایل htaccess
RUN echo 'RewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ index.php [QSA,L]' > /var/www/html/.htaccess

WORKDIR /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
