<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
 * @category   BigFish
 * @package    BigFish_Pmgw
 * @author     BIG FISH Ltd., paymentgateway [at] bigfish [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */
namespace BigFish\Pmgw\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class LogAbstract
 *
 * @package BigFish\Pmgw\Model
 */
class LogAbstract extends AbstractModel
{
    const CACHE_TAG = 'bigfish_pmgw_db';

    protected function _construct()
    {
        $this->_init('BigFish\Pmgw\Model\Resource\Log');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}

