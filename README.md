
# API Documentation

## Установка проекта

1. **Клонирование репозитория**
   ```sh
   git clone <your-repository-url>
   cd <your-project-folder>
   ```

2. **Установка зависимостей**
   ```sh
   composer install
   npm install
   ```

3. **Настройка окружения**
   ```sh
   cp .env.example .env
   ```
    - Настроить соединение с базой данных в `.env` (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)
    - Настроить хранилище (FILESYSTEM_DISK=public)

4. **Генерация ключа приложения**
   ```sh
   php artisan key:generate
   ```

5. **Миграции и наполнение базы данных**
   ```sh
   php artisan migrate --seed
   ```

## Запуск проекта

```sh
php artisan serve
```

## Генерация документации Swagger

1. **Установить пакет L5 Swagger (если не установлен)**
   ```sh
   composer require "darkaonline/l5-swagger"
   ```

2. **Сгенерировать документацию**
   ```sh
   php artisan l5-swagger:generate
   ```

3. **Открыть Swagger UI**
    - Перейти по адресу: `http://127.0.0.1:8000/api/documentation`

## Админка Filament

Для управления товарами, категориями, пользователями и их компонентами через панель администратора Filament выполните следующие шаги:

### Установка и настройка Filament

1. **Установить пакет Filament**
   ```sh
   composer require filament/filament
   ``
2. **Запуск админки**
   Для доступа к админке перейдите по адресу:
   ```
   http://127.0.0.1:8000/admin
   ```
   Введите ваши учетные данные для входа.

### Страницы и действия в админке:
- **Товары**: создание, редактирование, удаление товаров.
- **Категории**: управление категориями и подкатегориями товаров.
- **Пользователи**: управление пользователями, их данными и правами доступа.

## API Маршруты

### Аутентификация
- `POST /api/register` – Регистрация пользователя
- `POST /api/login` – Авторизация пользователя
- `POST /api/logout` – Выход (требует Bearer токен)
- `GET /api/profile` – Получить профиль пользователя (требует Bearer токен)

### Товары (Products)
- `GET /api/products` – Получить список всех товаров
- `POST /api/products` – Создать новый товар
- `GET /api/products/{id}` – Получить товар по ID
- `PUT /api/products/{id}` – Обновить товар
- `DELETE /api/products/{id}` – Удалить товар
- `GET /api/products/size/{size_slug}` – Получить товары по размеру

### Категории (Categories)
- `GET /api/categories` – Получить все категории
- `GET /api/categories/{slug}/parent` – Получить родительскую категорию
- `GET /api/categories/{slug}/children` – Получить дочерние категории

### Цвета (Colors)
- `GET /api/colors` – Получить все цвета
- `GET /api/colors/{id}/products` – Получить товары по цвету

### Адреса (Addresses) (Требуется аутентификация)
- `GET /api/addresses` – Получить список адресов пользователя
- `GET /api/addresses/primary` – Получить основной адрес пользователя
- `POST /api/addresses` – Добавить новый адрес
- `PUT /api/addresses/{address}` – Обновить адрес
- `DELETE /api/addresses/{address}` – Удалить адрес

### Размеры (Sizes)
- `GET /api/sizes` – Получить все размеры
- `POST /api/sizes` – Добавить новый размер
- `GET /api/sizes/{slug}/products` – Получить товары по размеру
- `DELETE /api/sizes/{id}` – Удалить размер

### Пользователи (Users)
- `GET /api/users` – Получить всех пользователей
- `GET /api/users/{id}` – Получить информацию о пользователе
- `POST /api/users` – Создать нового пользователя
- `PUT /api/users/{id}` – Обновить данные пользователя
- `DELETE /api/users/{id}` – Удалить пользователя

## Дополнительно

- **Настроить доступ к файлам (storage)**
  ```sh
  php artisan storage:link
  ```

---
