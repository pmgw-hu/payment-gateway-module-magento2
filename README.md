# Payment Gateway module for Magento 2

  - Use the `develop` branch to develop the module

  - Use the `master` branch to publish the module

## Install

### Requirements
  - Docker >= 1.11.0
  - Docker Compose >= 1.7.0
  - Traefik (https://gitlab.big.hu/big-fish/traefik)

### Setup Magento Community Edition (2.4.5)

  - Download Magento 2 release

    `https://github.com/magento/magento2/archive/refs/tags/2.4.5.zip`

  - Create a directory for Magento and uncompress code

    `mkdir magento2`

    `cd magento2`

    `unzip magento2-2.4.5.zip and copy the magento2-2.4.5 folder's files to magento2 project folder`

  - Clone module code

    `mkdir -p app/code/Bigfishpaymentgateway`

    `git clone -b develop https://gitlab.big.hu/payment-gateway/sdk-magento2.git app/code/Bigfishpaymentgateway/Pmgw/`

  - Symlink Docker specific files and folders

    `ln -s app/code/Bigfishpaymentgateway/Pmgw/.docker/`

    `ln -s app/code/Bigfishpaymentgateway/Pmgw/docker-compose.yml`

  - Set folder rights

    `chmod -R og+w app/etc/ generated/ pub/media/ pub/static/ var/ vendor/`

  - Set file rights

    `chmod 666 composer.*`

  - Set magento binary attributes

    `chmod uog+x bin/magento`

  - Build docker image

    `docker-compose build`

  - Start docker images

    `docker-compose up -d`

  - Install sub packages - composer install

    `docker exec -ti -u www-data magento2_web_1 /bin/bash`

    `composer update`

  - Setup Magento

    `docker exec -ti -u www-data magento2_web_1 /bin/bash`

    ```bash
    bin/magento setup:install \
    --timezone=Europe/Budapest \
    --currency=HUF \
    --admin-firstname=FISH \
    --admin-lastname=BIG \
    --admin-email=it@paymentgateway.hu \
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
    --use-rewrites=1 \
    --admin-use-security-key=0 \
    --search-engine=elasticsearch7 \
    --elasticsearch-host=elasticsearch \
    --elasticsearch-port=9200
    ```

  - Add repo to composer

    `composer config repositories.magento composer https://repo.magento.com`

    `Username: 7dd3d3f1d0c455c3b552de9760227e99`

    `Password: cfc50ef6b525d84aa20a8596a2200f6b`

    `Store credentials: Y`

  - Install example data set

    `bin/magento sampledata:deploy`

  - Enable PMGW module

    `bin/magento module:enable Bigfishpaymentgateway_Pmgw`

  - Upgrade @ DI compile

    `bin/magento setup:upgrade`

    `bin/magento setup:di:compile`

  - Reindex

    `bin/magento indexer:reindex`

  - Clear cache

    `bin/magento cache:clean`

  - Check webshop

    `http://magento2.dev.big.hu/`

  - Check admin interface

    `http://magento2.dev.big.hu/admin/`

### Setup Payment Gateway Module

  - Create `auth.json` in `magento2` directory

    ```json
    {
        "http-basic": {
            "repo.magento.com": {
                "username": "7dd3d3f1d0c455c3b552de9760227e99",
                "password": "cfc50ef6b525d84aa20a8596a2200f6b"
            }
        }
    }
    ```

  - Set Composer requirement

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

### Version

  - Modify the version number

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

  - Create a zipped package file from the module on the `master` branch

    `zip -r bigfishpaymentgateway-pmgw-[version number].zip . -x '.git/*' '.docker/*' '.gitignore' '.gitlab-ci.yml' 'docker-compose.yml'`

  - Use the validator

    `php validate_m2_package.php -d bigfishpaymentgateway-pmgw-[version number].zip`

### Publishing

  - Push `master` branch into https://github.com/bigfish-hu/payment-gateway-module-magento2 `master` branch

  - Create new release on https://github.com/bigfish-hu/payment-gateway-module-magento2

  - Upload zipped package to https://developer.magento.com/extension/extension/list/package_type/Extension/
