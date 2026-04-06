FROM php:8.3-fpm-alpine

RUN set -eux; \
    apk add --no-cache postgresql-client; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev; \
    docker-php-ext-install pdo pdo_pgsql; \
    apk del .build-deps

WORKDIR /var/www/html

COPY . /var/www/html

RUN if [ ! -f /var/www/html/app/config/config.php ]; then \
      cp /var/www/html/app/config/config.example.php /var/www/html/app/config/config.php; \
    fi

EXPOSE 9000

