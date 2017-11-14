<?php
/**
* BIG FISH Ltd.
* http://www.bigfish.hu
*
* @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
* @category   BigFish
* @package    BigFish_Pmgw
* @author     Gabor Huszak / BIG FISH Ltd. -> huszy [at] bigfish [dot] hu
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
* @copyright  Copyright (c) 2011, BIG FISH Ltd.
*/
namespace BigFish\Pmgw\Model\Provider;

class Cib extends Provider
{
    protected $_code  = 'bigfish_pmgw_cib';

    protected $_paymentMethod = 'CIB';

    protected $_isInitializeNeeded = true;

}