FROM php:7.4-apache

RUN apt-get update && apt-get install -y libpq-dev

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql

RUN docker-php-ext-install pdo pdo_pgsql pgsql

EXPOSE 80