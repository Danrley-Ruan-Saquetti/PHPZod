FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
  git \
  unzip \
  zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

CMD ["php-fpm"]
