# Payment Gateway module for Magento 2

## Install

### Requirements
  - Docker >= 1.11.0
  - Docker Compose >= 1.7.0
  - Traefik (https://gitlab.big.hu/big-fish/traefik)

### Setup Magento Community Edition (2.1.x or 2.2.x)

  - Download Magento with sample data (free registration required)

    `https://magento.com/tech-resources/download`

  - Create a directory for Magento and uncompress code

    `mkdir magento2`

    `cd magento2`

    `unzip /path/to/Magento-CE-[version]_sample_data-[release date].zip -d ./`

  - Clone module code

    `mkdir -p app/code/Bigfishpaymentgateway`

    `git clone -b master https://gitlab.big.hu/payment-gateway/sdk-magento2.git app/code/Bigfishpaymentgateway/Pmgw/`

  - Symlink Docker specific files and folders

    `ln -s app/code/Bigfishpaymentgateway/Pmgw/.docker/`

    `ln -s app/code/Bigfishpaymentgateway/Pmgw/docker-compose.yml`

  - Set folder rights

    `chmod -R og+w app/etc/ generated/ pub/media/ pub/static/ var/`

  - Set magento binary attributes

    `chmod uog+x bin/magento`

  - Build docker image

    `docker-compose build`

  - Start docker images

    `docker-compose up -d`

  - Setup Magento application via web interface

    `http://magento2.dev.big.hu/setup/`

  - OR via command line

    `docker exec -ti -u www-data magento2_web_1 /bin/bash`

    `cd /var/www/dev/magento2/`

    ```bash
    bin/magento setup:install \
    --timezone=Europe/Budapest \
    --currency=HUF \
    --admin-firstname=FISH \
    --admin-lastname=BIG \
    --admin-email=[email address] \
    --admin-user=bfadmin \
    --admin-password=NagyHal123 \
    --db-host=db \
    --db-name=magento \
    --db-user=magento \
    --db-password=magento \
    --use-secure=0 \
    --base-url=http://magento2.dev.big.hu/ \
    --use-secure-admin=0 \
    --backend-frontname=admin \
    --use-rewrites=0 \
    --admin-use-security-key=0
    ```

  - Reindex

    `bin/magento indexer:reindex`

  - Clear cache

    `bin/magento cache:clean`

  - Logout container

    `exit`

  - Update session handler (edit `app/etc/env.php` file)

    ```php
    array (
      'save' => 'memcached',
      'save_path' => 'session:11211',
    ),
    ```

  - Check webshop

    `http://magento2.dev.big.hu/`

  - Check admin interface

    `http://magento2.dev.big.hu/admin/`

### Setup Payment Gateway Module

  - After registration create and get Magento 2 access keys

    `https://marketplace.magento.com/customer/accessKeys/`

  - Create `auth.json` in `magento2` directory

    ```json
    {
        "http-basic": {
            "repo.magento.com": {
                "username": "[public key]",
                "password": "[private key]"
            }
        }
    }
    ```

  - Set Composer requirement

    `chmod og+w composer.*`

    `chmod -R og+w vendor/`

    `docker exec -ti -u www-data magento2_web_1 /bin/bash`

    `cd /var/www/dev/magento2/`

    `composer require bigfish/paymentgateway`

  - Logout container

    `exit`

  - Check module on admin interface

    `Admin / Stores / Configuration / Sales / Payment Methods`

  - Configure Payment Gateway

    `Store name: sdk_test`

    `Enabled: yes`

    `Test mode: yes`

    `API key: 86af3-80e4f-f8228-9498f-910ad`

## Development

### Magento command line

  - Command list

    `bin/magento list`

  - Urn schema generation for PhpStorm

    `bin/magento dev:urn-catalog:generate .idea/misc.xml`

  - Clear cache

    `bin/magento cache:clean`

  - Upgrade Magento application

    `bin/magento setup:upgrade`

  - Generate DI configuration

   `bin/magento setup:di:compile`

  - Set developer mode

    `php bin/magento deploy:mode:set developer`

## Extension publishing

### Check coding standards

  - Install Magento EQP Coding Standard

    `https://github.com/magento/marketplace-eqp`

    `docker exec -ti -u www-data magento2_web_1 /bin/bash`

    `cd /var/www/dev/magento2/`

    `composer create-project --repository=https://repo.magento.com magento/marketplace-eqp magento-coding-standard`

  - Code review

    `cd /var/www/dev/magento2/magento-coding-standard/`

    `vendor/bin/phpcs --config-set php7.0_path /usr/bin/php7.0`

    `vendor/bin/phpcs ../app/code/Bigfishpaymentgateway/Pmgw/ --standard=MEQP2 --severity=10 --extensions=php,phtml`

### Validation

  - Install Magento Marketplace tools

    `https://github.com/magento/marketplace-tools`

  - Create a zipped package file from the module on the github branch

    `zip -r [vendor name]-module-[module name]-[version number].zip . -x '.git/*' '.docker/*' '.gitignore' '.gitlab-ci.yml' 'docker-compose.yml'`

  - Use the validator

    `php validate_m2_package.php -d [vendor name]-module-[module name]-[version number].zip`
