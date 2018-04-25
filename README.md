BIG FISH Payment Gateway module manual installation and configure:

1. Backup your store.
2. Create app/code/Bigfishpaymentgateway/Pmgw folder.
3. unzip the module to app/code/Bigfishpaymentgateway/Pmgw folder.
4. Open terminal and enter these commands:
    bin/magento maintenance:enable
    bin/magento cache:flush
    bin/magento cache:disable
    bin/magento module:enable Bigfishpaymentgateway_Pmgw
    composer require bigfish/paymentgateway
    bin/magento setup:upgrade
    bin/magento setup:di:compile
    bin/magento setup:static-content:deploy
    bin/magento indexer:reindex
    bin/magento cache:enable
    bin/magento maintenance:disable
5. Login to Magento 2 Admin panel.
6. Configure the BIG FISH Payment Gateway module:
    Stores -> Configuration -> Sales -> Payment Methods
    Expand OTHER PAYMENT METHODS.
    Expand BIG FISH Payment Gateway Settings.
    Set the data below to test:
	Enabled: Yes
	Store name: sdk_test
	API key: 86af3-80e4f-f8228-9498f-910ad
	Test mode: Yes
    Enable any payment service provider.
    Save Config.

Important: make sure that the php path is correct in bin/magento file.
