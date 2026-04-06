FROM php:8.2-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        git \
        unzip \
    && docker-php-ext-install -j$(nproc) pdo_mysql mysqli curl \
    && rm -rf /var/lib/apt/lists/*

# Cloud Run：容器需聽環境變數 PORT（見 docker/entrypoint.sh）；部署時請確認服務埠與 PORT 一致（預設 8080）。
COPY composer.json composer.lock /var/www/html/
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && cd /var/www/html && composer install --no-dev --no-interaction --optimize-autoloader \
    && rm -rf /root/.composer

RUN a2enmod rewrite headers

# 預設站點根目錄（可被 compose 覆寫掛載）
ENV APACHE_DOCUMENT_ROOT=/var/www/html
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD []
