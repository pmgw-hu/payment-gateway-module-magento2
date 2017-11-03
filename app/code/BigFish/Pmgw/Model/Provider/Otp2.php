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
namespace BigFish\Pmgw\Model\Provider;

class Otp2 extends Provider
{

    protected $_formBlockType = 'paymentgateway/form_otp2';

    protected $_code  = 'bigfish_pmgw_otp2';

    protected $_paymentMethod = 'OTP2';

    protected $date;

}