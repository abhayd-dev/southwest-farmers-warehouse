FROM php:8.2-fpm

# 1️⃣ System dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    libxml2-dev \
    libpq-dev \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# 2️⃣ Install Node.js (for Vite build)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3️⃣ PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_pgsql \
    mbstring \
    zip \
    xml

# 4️⃣ Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# 5️⃣ Copy application source
COPY . .

# 6️⃣ Install PHP dependencies
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# 7️⃣ Build frontend assets
RUN npm install && npm run build

# 8️⃣ Create Laravel required directories (CRITICAL for Railway Volume)
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# 9️⃣ Expose Railway port
EXPOSE 8080

# 🔟 Start Laravel (storage link + server)
CMD sh -c "php artisan storage:link || true && php artisan serve --host=0.0.0.0 --port=$PORT"
