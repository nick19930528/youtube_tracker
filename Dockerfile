FROM php:8.2-apache-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql mysqli curl \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

# 預設站點根目錄（可被 compose 覆寫掛載）
ENV APACHE_DOCUMENT_ROOT=/var/www/html
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
