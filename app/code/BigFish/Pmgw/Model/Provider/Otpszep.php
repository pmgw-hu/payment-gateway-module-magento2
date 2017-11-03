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

class Otpszep extends Provider
{
	protected $_formBlockType = 'paymentgateway/form_otpszep';
	
    protected $_code  = 'bigfish_pmgw_otpszep';

    protected $_paymentMethod = 'OTPSZEP';

//    /**
//     * @var \Magento\Framework\App\Config\ScopeConfigInterface
//     */
//    protected $scopeConfig;
//
//    public function __construct(
//        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
//    ) {
//        $this->scopeConfig = $scopeConfig;
//    }
    public function getPaymentParams()
    {
//        try {
//            $params = parent::getPaymentParams();
//
//            $params['OtpCardPocketId']=$this->scopeConfig->getValue('paymentgateway/paymentgateway_otpszep/otpcardpocketid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//
//			if (!strlen($params['OtpCardPocketId'])) {
//				throw new \Exception();
//			}
//
//            return $params;
//        } catch (Exception $e) {
//            throw new \Magento\Framework\Exception\LocalizedException($this->_getHelper()->__('validation_invalidPocketId'));
//        }
    }
}