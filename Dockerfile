FROM php:8.3-cli

# System dependencies
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev zip curl \
    sqlite3 libsqlite3-dev \
    && docker-php-ext-install intl zip pdo pdo_mysql pdo_sqlite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Bun installation
RUN curl -fsSL https://bun.sh/install | bash
ENV PATH="/root/.bun/bin:$PATH"

WORKDIR /app

# Copy composer files first for better caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

# Copy application files
COPY . .

# Finish composer installation
RUN composer dump-autoload --optimize --no-dev

# Install JS dependencies and build assets
RUN bun install
RUN bun run build

# Set proper permissions for Laravel storage
RUN mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Create SQLite database file if it doesn't exist
RUN touch database/database.sqlite \
    && chmod 664 database/database.sqlite

# Laravel optimizations
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose port (Railway will override this with PORT env var)
EXPOSE 8000

# Start script - Run migrations and start server
CMD php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
