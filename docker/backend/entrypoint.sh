#!/bin/sh
set -e

# Migrations automatiques au démarrage
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Démarrage PHP-FPM en arrière-plan
php-fpm -D

# Démarrage Nginx au premier plan
nginx -g "daemon off;"