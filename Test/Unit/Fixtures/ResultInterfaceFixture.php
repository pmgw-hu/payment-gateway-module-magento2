<?php
namespace BigFish\Pmgw\Test\Unit\Fixtures;

use Magento\Framework\Controller\ResultInterface;

interface ResultInterfaceFixture extends ResultInterface
{
    /**
     * @param string $url
     */
    public function setUrl($url);

}
