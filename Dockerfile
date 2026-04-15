FROM composer:2

WORKDIR /app

COPY . .

# Installer dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Générer clé si pas encore faite
RUN php artisan key:generate || true

# Donner permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
