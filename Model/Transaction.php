<?php
namespace Bigfishpaymentgateway\Pmgw\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Transaction extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'bigfishpaymentgateway_pmgw_transaction';

    protected function _construct()
    {
        $this->_init('Bigfishpaymentgateway\Pmgw\Model\Source\Transaction');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

}
