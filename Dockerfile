# Используем официальный PHP-образ с Apache
FROM php:8.1-apache

# Устанавливаем необходимые расширения
RUN docker-php-ext-install pdo pdo_mysql

# Копируем проект
COPY . /var/www/html

# Устанавливаем зависимости
RUN apt-get update && apt-get install -y unzip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    git

RUN composer install --no-dev --optimize-autoloader

RUN php artisan key:generate

RUN php artisan migrate

RUN php artisan l5-swagger:generate

# Разрешаем доступ
RUN chown -R www-data:www-data /var/www/html

# Открываем порт
EXPOSE 80
