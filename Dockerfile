# ---- Node build stage ----
FROM node:20-alpine AS node-build

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build


# ---- PHP production stage ----
FROM php:8.2-fpm-alpine AS app

# System dependencies
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    git

# PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    opcache

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies (production only)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Copy application source
COPY . .

# Copy compiled frontend assets from node build stage
COPY --from=node-build /app/public/build ./public/build

# Storage & cache directories
RUN mkdir -p storage/framework/{sessions,views,cache} \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Run post-install scripts now that full app is present
RUN composer run-script post-autoload-dump --no-interaction

# PHP-FPM listens on 9000
EXPOSE 9000

CMD ["php-fpm"]


# ---- Nginx stage ----
FROM nginx:alpine AS nginx-stage

# Copy public files (including compiled Vite assets) from app stage
COPY --from=app /var/www/html/public /var/www/html/public

# Nginx config
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
