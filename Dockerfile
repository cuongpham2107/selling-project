FROM php:8.3-cli

# System deps
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev zip curl \
    && docker-php-ext-install intl zip pdo pdo_mysql

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node (dùng Bun cho đúng với project của bạn)
RUN curl -fsSL https://bun.sh/install | bash
ENV PATH="/root/.bun/bin:$PATH"

WORKDIR /app
COPY . .

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader

# Install JS deps + build Vite
RUN bun install
RUN bun run build

# Laravel cache (optional nhưng nên có)
RUN php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear

CMD php artisan serve --host=0.0.0.0 --port=${PORT}
