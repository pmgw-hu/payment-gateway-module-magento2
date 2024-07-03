> Repository github.com/bigfish-hu/payment-gateway-module-magento2 (bigfishpaymentgateway/pmgw) is abandoned, you should avoid using it.<br>
><br>
> Use https://github.com/pmgw-hu/payment-gateway-module-magento2 ([pmgw/payment-gateway-module-magento2](https://packagist.org/packages/pmgw/payment-gateway-module-magento2)) instead.

# BIG FISH Payment Gateway module for Magento 2

## Latest supported Magento and PHP versions

Magento 2.4.6-p2<br />
PHP 8.2

## The steps required to use our services are as follows

#### 1. Contracting with us:

The online contracting process can be initiated by clicking on the following link and choosing the suitable package: <a href="https://www.paymentgateway.hu/arak" target="_blank">Tariff packages and contracting</a>

The language of contract and communication is Hungarian.

#### 2. Contracting with the selected payment service provider(s) for online card acceptance:
Our company is a payment technology platform (not a bank or a payment service provider), therefore to use our solution, you need to have an active contract with at least one PSP available on our system.

The list of payment service providers available in our system can be found under the following link: <a href="https://www.paymentgateway.hu/partnereink" target="_blank">Our partners</a>

#### 3. Connecting to BIG FISH Payment Gateway:
Your IT personnel can examine the integration opportunities even before signing the contract with us. The module is free to use in the test environment. Using the production environment requires an active contract with us and the <a href="https://www.paymentgateway.hu/fejlesztoknek/egyeb/elesitesi-kovetelmenyek" target="_blank">requirements</a> must be met.

Should you need any further information, please do not hesitate to contact us through the [it@paymentgateway.hu](mailto:it@paymentgateway.hu) email address.

## Manual installation and configure

#### 1. Backup your store.

#### 2. Create app/code/Bigfishpaymentgateway/Pmgw folder.

#### 3. unzip the module to app/code/Bigfishpaymentgateway/Pmgw folder.

#### 4. Open terminal and enter these commands:

 * `bin/magento maintenance:enable`

 * `bin/magento cache:flush`

 * `bin/magento cache:disable`

 * `bin/magento module:enable Bigfishpaymentgateway_Pmgw`

 * `composer require pmgw/payment-gateway-php7-sdk`

 * `bin/magento setup:upgrade`

 * `bin/magento setup:di:compile`

 * `bin/magento setup:static-content:deploy`

 * `bin/magento indexer:reindex`

 * `bin/magento cache:enable`

 * `bin/magento maintenance:disable`

#### 5. Login to Magento 2 Admin panel.

#### 6. Configure the BIG FISH Payment Gateway module:

 * Stores -> Configuration -> Sales -> Payment Methods

 * Expand OTHER PAYMENT METHODS.

 * Expand BIG FISH Payment Gateway Settings.

 * Set the data below to test:

   Enabled: Yes<br />
   Store name: sdk_test<br />
   API key: 86af3-80e4f-f8228-9498f-910ad<br />
   Test mode: Yes

 * Enable any payment service provider.

 * Save Config.

**Important: make sure that the php path is correct in bin/magento file.**
