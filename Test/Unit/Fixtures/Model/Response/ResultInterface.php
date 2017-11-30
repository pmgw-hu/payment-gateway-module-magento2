<?php
namespace BigFish\Pmgw\Test\Unit\Fixtures\Model\Response;

interface ResultInterface extends \Magento\Framework\Controller\ResultInterface
{
    /**
     * @param string $url
     */
    public function setUrl($url);

}
