<?php
namespace Bigfishpaymentgateway\Pmgw\Model\Source\Transaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Bigfishpaymentgateway\Pmgw\Model\Transaction',
            'Bigfishpaymentgateway\Pmgw\Model\Source\Transaction'
        );
    }

}
