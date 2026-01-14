FROM php:8.2-fpm

# 1️⃣ System dependencies
# Added: libxml2-dev (Fixes your error), gnupg (For Node setup)
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
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# 2️⃣ Install Node.js (Required for 'npm run build')
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 3️⃣ PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
# Combined install command for cleaner layers
RUN docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mbstring zip xml

# 4️⃣ Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy files
COPY . .

# 5️⃣ Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 6️⃣ Frontend build (This will now work because Node is installed)
RUN npm install && npm run build

# 7️⃣ Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# 8️⃣ Start Command for Railway
# Railway provides a dynamic PORT. php-fpm cannot handle HTTP directly.
# We use artisan serve bound to 0.0.0.0 and the Railway PORT.
EXPOSE 8080
CMD php artisan serve --host=0.0.0.0 --port=$PORT