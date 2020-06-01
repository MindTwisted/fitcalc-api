FROM php:7.4-fpm

RUN apt-get -y update && \
    apt-get install -y git zip unzip libicu-dev libxslt-dev

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

RUN docker-php-ext-configure intl && docker-php-ext-install intl

RUN docker-php-ext-install xsl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer