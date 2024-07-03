<?php
/**
 * BIG FISH Payment Services Ltd.
 * https://paymentgateway.hu
 *
 * @title      BIG FISH Payment Gateway module for Magento 2
 * @category   BigFish
 * @package    Bigfishpaymentgateway_Pmgw
 * @author     BIG FISH Payment Services Ltd., it [at] paymentgateway [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2024, BIG FISH Payment Services Ltd.
 */
namespace Bigfishpaymentgateway\Pmgw\Test\Unit\Fixtures;

use Magento\Store\Api\Data\StoreInterface;

interface StoreInterfaceFixture extends StoreInterface
{
    /**
     * @param string $type
     * @return string
     */
    public function getBaseUrl($type);

    /**
     * @return string
     */
    public function getLocaleCode();

}
