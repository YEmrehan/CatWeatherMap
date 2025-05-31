# ./Dockerfile
FROM php:8.2-apache

# Gerekli PHP eklentilerini kur
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Composer yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache için mod_rewrite aktif et (Laravel gibi frameworklerde şart olabilir)
RUN a2enmod rewrite

# Varsayılan çalışma dizini
WORKDIR /var/www/html

# Kaynak dosyaları container içine kopyalama (isteğe bağlı, zaten volume ile de bağlanıyor)
# COPY ./src /var/www/html
