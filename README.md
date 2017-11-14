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

    `mkdir magento`

    `unzip /path/to/Magento-CE-[version]_sample_data-[release date].zip -d /path/to/magento/`

  - Clone module code

    `cd magento`

    `mkdir -p app/code/BigFish`

    `git clone -b master https://gitlab.big.hu/payment-gateway/sdk-magento2.git app/code/BigFish/Pmgw/`

  - Symlink Docker specific files and folders

    `ln -s app/code/BigFish/Pmgw/.docker/`

    `ln -s app/code/BigFish/Pmgw/docker-compose.yml`

  - Set folder rights

    `chmod -R og+w app/etc/ pub/media/ pub/static/ var/`

  - Set magento binary attributes

    `chmod uog+x bin/magento`

  - Build docker image

    `docker-compose build`

  - Start docker images

    `docker-compose up -d`

  - Setup Magento application via web interface

    `http://magento.dev.big.hu/setup/`

  - or via command line

    `docker exec -ti -u www-data magento_web_1 /bin/bash`

    `cd /var/www/dev/magento/`

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
    --base-url=http://magento.dev.big.hu/ \
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

    `http://magento.dev.big.hu/`

  - Check admin interface

    `http://magento.dev.big.hu/admin/`

### Setup Payment Gateway Module

  - After registration create and get Magento 2 access keys

    `https://marketplace.magento.com/customer/accessKeys/`

  - Create `auth.json` in `magento` directory

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

    `docker exec -ti -u www-data magento_web_1 /bin/bash`

    `cd /var/www/dev/magento/`

    `composer require bigfish/paymentgateway`

  - Enable module

    `bin/magento module:enable BigFish_Pmgw`

    `bin/magento setup:upgrade`

    `bin/magento setup:di:compile`

  - Logout container

    `exit`

  - Check module on admin interface

    `Admin / Stores / Configuration / Sales / Payment Methods`

## Development

### Magento command line

  - Urn schema generation for PhpStorm

    `bin/magento dev:urn-catalog:generate .idea/misc.xml`

  - Clear cache type(s)

    `bin/magento cache:clear [type, eg: config]`

  - Generate DI configuration

   `bin/magento setup:di:compile`

  - Set developer mode

    `php bin/magento deploy:mode:set developer`
