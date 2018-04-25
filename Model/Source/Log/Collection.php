<?php
namespace Bigfishpaymentgateway\Pmgw\Model\Source\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Bigfishpaymentgateway\Pmgw\Model\Log',
            'Bigfishpaymentgateway\Pmgw\Model\Source\Log'
        );
    }

}
