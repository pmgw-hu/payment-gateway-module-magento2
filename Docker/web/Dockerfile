#
# Web node
#
FROM docker.big.hu/development/nginx-php:7.0.7
MAINTAINER BIG FISH <cafe@bigfish.hu>

#
# Install extra PHP modules
#
RUN apt-get update

RUN apt-get install -t jessie-bigfish -y \
    php7.0-memcached \
    php7.0-json \
    php7.0-dom \
    php7.0-SimpleXML \
    php7.0-xml \
    php7.0-bcmath \
    php7.0-mbstring \
    php7.0-soap \
    php7.0-intl \
    php7.0-zip


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
# Get composer
#
RUN cd /usr/local/bin \
    && wget -q https://getcomposer.org/download/1.0.0/composer.phar \
    && chmod ugo+rx composer.phar \
    && ln -s composer.phar composer

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
# Docroot
#
RUN mkdir -p /var/www/dev/magento \
    && chown -R www-data:www-data /var/www/dev/magento \
    && rm -rf /var/www/dev/magento/*

VOLUME ["/var/www/dev/magento"]

#
# XHPROF settings
#
#ADD ./src/php/20-xhprof.ini /etc/php/7.0/mods-available/20-xhprof.ini
#RUN ln -s /etc/php/7.0/mods-available/20-xhprof.ini /etc/php/7.0/cli/conf.d/20-xhprof.ini
#RUN ln -s /etc/php/7.0/mods-available/20-xhprof.ini /etc/php/7.0/fpm/conf.d/20-xhprof.ini


