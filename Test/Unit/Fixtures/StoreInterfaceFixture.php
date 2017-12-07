<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
 * @category   BigFish
 * @package    BigFish_Pmgw
 * @author     BIG FISH Ltd., paymentgateway [at] bigfish [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */
namespace BigFish\Pmgw\Test\Unit\Fixtures;

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
