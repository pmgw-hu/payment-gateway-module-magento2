<?php
namespace Bigfishpaymentgateway\Pmgw\Model\Source;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transaction extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('bigfish_paymentgateway', 'paymentgateway_id');
    }

}
