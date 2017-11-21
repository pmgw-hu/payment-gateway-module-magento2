<?php
namespace BigFish\Pmgw\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Log extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'bigfish_pmgw_log';

    protected function _construct()
    {
        $this->_init('BigFish\Pmgw\Model\Resource\Log');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}
