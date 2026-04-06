#!/bin/bash
set -e
cd /var/www/html
if [ -f composer.json ] && [ ! -f vendor/autoload.php ]; then
    composer install --no-dev --no-interaction --optimize-autoloader
fi

# Cloud Run / Cloud Build Deploy：平台會注入 PORT（預設 8080），流量只打進此埠。
# 本機 Docker Compose 通常不設 PORT，Apache 維持聽 80，再由 compose 對映 8080:80。
APACHE_PORT="${PORT:-80}"
if [ -f /etc/apache2/ports.conf ]; then
    sed -i "s/^Listen .*/Listen ${APACHE_PORT}/" /etc/apache2/ports.conf
fi
if [ -f /etc/apache2/sites-available/000-default.conf ]; then
    sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${APACHE_PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

if [ -x /usr/local/bin/docker-php-entrypoint ]; then
    exec /usr/local/bin/docker-php-entrypoint apache2-foreground "$@"
fi
exec apache2-foreground "$@"
