FROM php:8.2-fpm

# 1️⃣ System dependencies (Includes libpq-dev for Postgres)
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

# 2️⃣ Install Node.js (For building public/build assets)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3️⃣ PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql mbstring zip xml

# OPcache: compile the PHP code once instead of on every request. The built-in
# server (`artisan serve`, SAPI "cli-server") follows opcache.enable.
RUN docker-php-ext-install opcache \
    && { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
    } > /usr/local/etc/php/conf.d/zz-opcache.ini

# Serve requests in parallel instead of one at a time (needs `serve --no-reload`).
# A Railway variable with the same name overrides this.
ENV PHP_CLI_SERVER_WORKERS=4

# 4️⃣ Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy ALL files (includes public/images, excludes ignored files)
COPY . .

# 5️⃣ Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 6️⃣ Frontend build (Generates public/build/ for JS & CSS)
RUN npm install && npm run build

# 7️⃣ Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# 8️⃣ Start Command (CRITICAL CHANGE)
# This links the storage folder, runs migrations, caches config/routes/views
# (built here, not at image build, so they use Railway's variables; if any
# cache step fails, all caches are cleared and the app runs uncached as
# before), AND starts the server with PHP_CLI_SERVER_WORKERS workers.
EXPOSE 8080
CMD sh -c "php artisan storage:link && php artisan migrate --force && { php artisan config:cache && php artisan route:cache && php artisan view:cache || php artisan optimize:clear || true; } && exec php artisan serve --host=0.0.0.0 --port=$PORT --no-reload"