<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BigFish\Pmgw\Test\Unit\Model\Ui;

use BigFish\Pmgw\Gateway\Http\Client\AuthorizeClient;
use BigFish\Pmgw\Model\Ui\ConfigProvider;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $configProvider = new ConfigProvider();

        static::assertEquals(
            [
                'payment' => [
                    ConfigProvider::CODE => [
                        'transactionResults' => [
                            AuthorizeClient::SUCCESS => __('Success'),
                            AuthorizeClient::FAILURE => __('Fraud')
                        ]
                    ]
                ]
            ],
            $configProvider->getConfig()
        );
    }
}
