<?php
namespace BigFish\Pmgw\Model\Resource;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Log extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('bigfish_paymentgateway_log', 'log_id');
    }

}
