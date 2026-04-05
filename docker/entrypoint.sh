#!/bin/bash
set -e
cd /var/www/html
if [ -f composer.json ] && [ ! -f vendor/autoload.php ]; then
    composer install --no-dev --no-interaction --optimize-autoloader
fi
if [ -x /usr/local/bin/docker-php-entrypoint ]; then
    exec /usr/local/bin/docker-php-entrypoint apache2-foreground "$@"
fi
exec apache2-foreground "$@"
