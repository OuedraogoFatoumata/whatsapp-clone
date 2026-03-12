FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git curl zip unzip nodejs npm \
    libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --optimize-autoloader --no-dev
RUN npm install && npm run build

RUN php artisan config:cache && php artisan route:cache

EXPOSE 80

CMD ["sh", "-c", "php artisan migrate --force && apache2-foreground"]