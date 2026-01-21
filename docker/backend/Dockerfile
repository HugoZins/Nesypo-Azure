FROM php:8.3-cli

# Dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    curl \
    && docker-php-ext-install pdo pdo_pgsql

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier le projet (utile pour build initial)
COPY ./backend /var/www/html

# Installer les dépendances au build
RUN composer install --no-interaction --optimize-autoloader

# Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

EXPOSE 8000

CMD ["symfony", "server:start", "--no-tls", "--allow-http", "--port=8000", "--listen-ip=0.0.0.0"]
