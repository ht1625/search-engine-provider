# 1. PHP FPM resmi imajını kullan
FROM php:8.2-fpm

# 2. Gerekli paketleri kur
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    build-essential \
    nano \
    cron \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip \
    && rm -rf /var/lib/apt/lists/*

# 3. Composer yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Çalışma dizinini ayarla
WORKDIR /var/www/html

# 5. Proje dosyalarını kopyala
COPY . .

# 6. Laravel için gerekli izinleri ayarla (özellikle storage ve bootstrap/cache)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Laravel server portu
EXPOSE 9000

# 8. PHP-FPM başlat
CMD ["php-fpm"]
