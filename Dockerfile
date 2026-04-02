FROM php:8.4-cli-alpine

RUN apk add --no-cache curl git unzip $PHPIZE_DEPS pcre2-dev \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apk del $PHPIZE_DEPS pcre2-dev

RUN echo 'pcov.enabled=1' >> /usr/local/etc/php/conf.d/docker-php-ext-pcov.ini

WORKDIR /app
