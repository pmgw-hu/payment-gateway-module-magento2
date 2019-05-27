#
# Web node
#
FROM docker.big.hu/development/nginx-php:7.2
MAINTAINER BIG FISH <cafe@bigfish.hu>

RUN printf "deb http://deb.debian.org/debian/ jessie main\ndeb http://security.debian.org/ jessie/updates main\ndeb http://repo.big.hu/debian jessie-backports main\ndeb http://repo.big.hu/debian jessie-bigfish main sury-php" > /etc/apt/sources.list

RUN wget -qO - https://repo.big.hu/repo.big.hu.key | apt-key add -

#
# Install extra PHP modules
#
RUN apt-get update

RUN apt-get install -t jessie-bigfish -y \
    php7.2-bcmath=${PHP7_VERSION} \
    php7.2-intl=${PHP7_VERSION} \
    php7.2-mbstring=${PHP7_VERSION} \
    php7.2-soap=${PHP7_VERSION} \
    php7.2-xml=${PHP7_VERSION} \
    php7.2-zip=${PHP7_VERSION}

RUN apt-get install -y zip \
    unzip

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
ADD ./src/php/000-php-settings.ini /etc/php/7.2/mods-available/000-php-settings.ini
RUN ln -s /etc/php/7.2/mods-available/000-php-settings.ini /etc/php/7.2/cli/conf.d/000-php-settings.ini
RUN ln -s /etc/php/7.2/mods-available/000-php-settings.ini /etc/php/7.2/fpm/conf.d/000-php-settings.ini

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
RUN mkdir -p /var/www/dev/magento2 \
    && chown -R www-data:www-data /var/www/dev/magento2 \
    && rm -rf /var/www/dev/magento2/*

VOLUME ["/var/www/dev/magento2"]
