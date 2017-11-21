<?php
namespace BigFish\Pmgw\Model\Resource\Log;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'BigFish\Pmgw\Model\Log',
            'BigFish\Pmgw\Model\Resource\Log'
        );
    }

}
