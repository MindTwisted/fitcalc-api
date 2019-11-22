FROM php:7.2-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends git zip unzip

RUN cd "/usr/local/bin/" \
    docker-php-ext-install ctype \
    docker-php-ext-install iconv \
    docker-php-ext-install json \
    docker-php-ext-install pcre \
    docker-php-ext-install session \
    docker-php-ext-install simplexml \
    docker-php-ext-install tokenizer

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

RUN ln -s /app/public/index.php /var/www/html/index.php