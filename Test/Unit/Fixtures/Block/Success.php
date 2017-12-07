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
namespace BigFish\Pmgw\Test\Unit\Fixtures\Block;

use Magento\Framework\View\Element\Template;

class Success extends \BigFish\Pmgw\Block\Success
{
    /**
     * @return Template
     */
    public function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

}
