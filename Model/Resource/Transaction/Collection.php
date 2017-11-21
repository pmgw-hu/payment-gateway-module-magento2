<?php
namespace BigFish\Pmgw\Model\Resource\Transaction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'BigFish\Pmgw\Model\Transaction',
            'BigFish\Pmgw\Model\Resource\Transaction'
        );
    }

}
