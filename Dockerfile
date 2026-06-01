FROM php:8.2-cli

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libicu-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-install intl mysqli pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php-entrypoint.sh /usr/local/bin/php-entrypoint
RUN chmod +x /usr/local/bin/php-entrypoint

EXPOSE 8080

ENTRYPOINT ["php-entrypoint"]
CMD ["php", "spark", "serve", "--host", "0.0.0.0", "--port", "8080"]
