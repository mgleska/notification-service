FROM php:8.2-alpine

COPY . /app
WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_pgsql && \
    curl -o /usr/local/bin/composer https://getcomposer.org/download/latest-stable/composer.phar && \
    chmod +x /usr/local/bin/composer && \
    composer install && \
    php bin/console nelmio:apidoc:dump --format=json > public/api.json && \
    php bin/console nelmio:apidoc:dump --format=html > public/api.html && \
    php bin/console nelmio:apidoc:dump --format=yaml > public/api.yaml && \
    chmod +x /app/docker/wait-for

CMD [ "sleep", "36000" ]
