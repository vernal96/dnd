FROM php:8.3-cli-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-libs \
    libpq \
    postgresql-client \
    unzip \
    zip \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    libpq-dev \
    libxml2-dev \
    linux-headers \
    oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    dom \
    intl \
    pcntl \
    pdo_pgsql \
    sockets \
    xml \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps \
    && rm -rf /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint

RUN chmod +x /usr/local/bin/app-entrypoint

ENTRYPOINT ["app-entrypoint"]

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
