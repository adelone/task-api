FROM php:8.4-fpm

RUN docker-php-ext-install pdo_mysql bcmath

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

WORKDIR /var/www/html

USER www-data

CMD ["php-fpm"]