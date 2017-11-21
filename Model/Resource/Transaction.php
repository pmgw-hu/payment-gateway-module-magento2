<?php
namespace BigFish\Pmgw\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transaction extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('bigfish_paymentgateway', 'paymentgateway_id');
    }

}
