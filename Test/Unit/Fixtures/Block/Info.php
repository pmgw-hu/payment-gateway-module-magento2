<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
 * @category   BigFish
 * @package    Bigfishpaymentgateway_Pmgw
 * @author     BIG FISH Ltd., paymentgateway [at] bigfish [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */
namespace Bigfishpaymentgateway\Pmgw\Test\Unit\Fixtures\Block;

class Info extends \Bigfishpaymentgateway\Pmgw\Block\Info
{
    /**
     * @param string $field
     * @return \Magento\Framework\Phrase
     */
    public function getLabel($field)
    {
        return parent::getLabel($field);
    }

}
