FROM php:7.2-apache

RUN apt-get update && \
    apt-get install -y git zip unzip

RUN cd "/usr/local/bin/" \
    docker-php-ext-install -j$(nproc) ctype \
    docker-php-ext-install -j$(nproc) iconv \
    docker-php-ext-install -j$(nproc) json \
    docker-php-ext-install -j$(nproc) pcre \
    docker-php-ext-install -j$(nproc) session \
    docker-php-ext-install -j$(nproc) simplexml \
    docker-php-ext-install -j$(nproc) tokenizer \
    docker-php-ext-install -j$(nproc) mysqli \
    docker-php-ext-install -j$(nproc) pdo

RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-configure mysqli --with-mysqli=mysqlnd \
    && docker-php-ext-install pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite
RUN rm -rf /var/www/html && ln -s /app/public /var/www/html