<?php
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
