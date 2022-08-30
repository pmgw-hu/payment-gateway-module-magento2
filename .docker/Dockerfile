FROM alpine:3.16.1 as base
MAINTAINER BIG FISH <cafe@bigfish.hu>

ARG PHP_VERSION=81
ARG RELEASE=null

ENV PHP_VERSION=${PHP_VERSION}
ENV RELEASE=${RELEASE}

RUN apk add --update --no-cache \
    curl \
    git \
    bash \
    tzdata \
    supervisor \
    make \
    nginx \
    php${PHP_VERSION} \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-common \
    php${PHP_VERSION}-phar \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-pcntl \
    php${PHP_VERSION}-posix \
    php${PHP_VERSION}-ctype \
    php${PHP_VERSION}-xsl \
    php${PHP_VERSION}-opcache \
    php${PHP_VERSION}-exif \
    php${PHP_VERSION}-fileinfo \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-json \
    php${PHP_VERSION}-dom \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-xmlreader \
    php${PHP_VERSION}-xmlwriter \
    php${PHP_VERSION}-tokenizer \
    php${PHP_VERSION}-simplexml \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-iconv \
    php${PHP_VERSION}-openssl \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-pecl-redis \
    php${PHP_VERSION}-pdo \
    php${PHP_VERSION}-pdo_pgsql \
    php${PHP_VERSION}-pdo_mysql \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-ftp \
    php${PHP_VERSION}-soap \
    php${PHP_VERSION}-dom \
    php${PHP_VERSION}-simplexml \
    php${PHP_VERSION}-sodium \
    php${PHP_VERSION}-sockets \
  && rm -rf /var/cache/apk/* \
  && curl -o /usr/local/bin/composer https://getcomposer.org/composer-stable.phar \
  && chmod ugo+rx /usr/local/bin/composer \
  # Create user
  && adduser -S www-data -G www-data \
  # Create document root
  && mkdir -p /var/www/dev/magento2 \
  && rm -rf /var/www/dev/magento2/* \
  && chown -R www-data:www-data /var/www/dev/magento2 \
  # Set up PHP
  && ln -sf /usr/bin/php${PHP_VERSION} /usr/bin/php \
  && ln -sf /usr/sbin/php-fpm${PHP_VERSION} /usr/sbin/php-fpm \
  && ln -sf /dev/stdout /var/log/php_error.log \
  && chown -R www-data:www-data /var/log/php_error.log \
  # Set up NGINX
  && chmod +x /var/lib/nginx -R \
  && mkdir /var/tmp/nginx \
  && chown -R www-data:www-data /var/tmp/nginx

#
# Set timezone (https://wiki.alpinelinux.org/wiki/Setting_the_timezone)
#
ENV TZ=Europe/Budapest

RUN cp /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo "${TZ}" > /etc/timezone

WORKDIR "/var/www/dev/magento2"

COPY --chown=www-data:www-data . .
RUN touch /var/log/app.log && chmod og+w /var/log/app.log

COPY src/etc/supervisor/supervisord.conf /etc/supervisord.conf
COPY src/etc/php/conf.d/000-php-settings.ini /etc/php${PHP_VERSION}/conf.d/000-php-settings.ini
COPY src/etc/php/php-fpm.d/www.conf /etc/php${PHP_VERSION}/php-fpm.d/www.conf
COPY src/etc/php/php-fpm.conf /etc/php${PHP_VERSION}/php-fpm.conf
COPY src/etc/nginx/nginx.conf /etc/nginx/nginx.conf
COPY src/etc/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY src/etc/nginx/conf.d/app.conf /etc/nginx/conf.d/app.conf
COPY src/etc/nginx/conf.d/snippet /etc/nginx/conf.d/snippet

CMD /usr/bin/supervisord -n -c /etc/supervisord.conf

EXPOSE 80

STOPSIGNAL SIGQUIT
