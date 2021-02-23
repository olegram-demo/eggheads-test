FROM v8js/v8js:8.6-php-7.4

RUN apt-get update && apt-get install -y \
  git \
  zip \
  php7.4-mysqli \
  php7.4-mbstring \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
