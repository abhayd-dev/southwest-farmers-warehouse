FROM php:8.2-fpm

# 1️⃣ System dependencies
# Added: libpq-dev (Required for PostgreSQL)
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

# 2️⃣ Install Node.js (Required for 'npm run build')
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3️⃣ PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# CHANGED: Added 'pdo_pgsql' here
RUN docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql mbstring zip xml

# 4️⃣ Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy files
COPY . .

# 5️⃣ Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 6️⃣ Frontend build
RUN npm install && npm run build

# 7️⃣ Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# 8️⃣ Start Command
EXPOSE 8080
CMD php artisan serve --host=0.0.0.0 --port=$PORT