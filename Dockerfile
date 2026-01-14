FROM php:8.2-fpm

# 1️⃣ System dependencies (stable on Bookworm)
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
    && rm -rf /var/lib/apt/lists/*

# 2️⃣ Configure GD (MUST be separate)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# 3️⃣ Install PHP extensions (split = stable)
RUN docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-install pdo pdo_mysql mbstring zip
RUN docker-php-ext-install xml

# 4️⃣ Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# 5️⃣ Laravel deps
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 6️⃣ Frontend build
RUN npm install && npm run build

# 7️⃣ Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
