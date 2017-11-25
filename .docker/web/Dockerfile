#
# Web node
#
FROM docker.big.hu/development/nginx-php:7.0
MAINTAINER BIG FISH <cafe@bigfish.hu>

#
# Install extra PHP modules
#
RUN apt-get update

RUN apt-get install -t jessie-bigfish -yq \
    php7.0-bcmath=${PHP7_VERSION} \
    php7.0-intl=${PHP7_VERSION} \
    php7.0-mbstring=${PHP7_VERSION} \
    php7.0-soap=${PHP7_VERSION} \
    php7.0-xml=${PHP7_VERSION} \
    php7.0-zip=${PHP7_VERSION}

#
# Get composer
#
RUN cd /usr/local/bin \
    && wget -q https://getcomposer.org/composer.phar \
    && chmod ugo+rx composer.phar \
    && ln -s composer.phar composer

#
# PHP settings
#
ADD ./src/php/000-php-settings.ini /etc/php/7.0/mods-available/000-php-settings.ini
RUN ln -s /etc/php/7.0/mods-available/000-php-settings.ini /etc/php/7.0/cli/conf.d/000-php-settings.ini
RUN ln -s /etc/php/7.0/mods-available/000-php-settings.ini /etc/php/7.0/fpm/conf.d/000-php-settings.ini

#
# Forward PHP error log to docker log collector
#
RUN ln -sf /dev/stderr /var/log/php_error.log

#
# Set bash for www-data user
#
RUN usermod -s /bin/bash www-data

#
# Nginx config
#
RUN rm -Rf /etc/nginx/conf.d/*
ADD ./src/nginx/nginx.conf /etc/nginx/nginx.conf
ADD ./src/nginx/default.conf /etc/nginx/conf.d/default.conf

#
# Postfix config
#
ADD ./src/postfix/main.cf /etc/postfix/main.cf

#
# Docroot
#
RUN mkdir -p /var/www/dev/magento \
    && chown -R www-data:www-data /var/www/dev/magento \
    && rm -rf /var/www/dev/magento/*

VOLUME ["/var/www/dev/magento"]
