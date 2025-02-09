# Используем PHP 8.3 с Apache
FROM php:8.3-apache

# Устанавливаем необходимые расширения
RUN apt-get update && apt-get install -y \
    unzip \
    curl \
    git \
    libpq-dev \
    libonig-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip mbstring

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Копируем проект
WORKDIR /var/www/html
COPY . .

# Устанавливаем права
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html

# Проверяем файлы перед composer install
RUN ls -la /var/www/html

# Устанавливаем зависимости с флагами безопасности
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Генерируем ключ
RUN php artisan key:generate

# Миграции + Swagger
RUN php artisan migrate --force
RUN php artisan l5-swagger:generate || true

# Открываем порт 80
EXPOSE 80

# Запуск Apache
CMD ["apache2-foreground"]
