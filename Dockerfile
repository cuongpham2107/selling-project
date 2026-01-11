FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev zip \
    && docker-php-ext-install intl zip pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php artisan key:generate --force || true \
 && php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear


CMD php artisan serve --host=0.0.0.0 --port=${PORT}
