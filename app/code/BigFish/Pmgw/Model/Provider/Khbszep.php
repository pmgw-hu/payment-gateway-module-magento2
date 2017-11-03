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

use Magento\Framework\App\Config\ScopeConfigInterface;

class Khbszep extends Provider
{
	protected $_formBlockType = 'paymentgateway/form_khbszep';
	
    protected $_code  = 'bigfish_pmgw_khbszep';

    protected $_paymentMethod = 'KHBSZEP';

//    /**
//     * @var \Magento\Framework\App\Config\ScopeConfigInterface
//     */
//    protected $scopeConfig;
//
//    public function __construct(ScopeConfigInterface $scopeConfig)
//    {
//        $this->scopeConfig = $scopeConfig;
//    }

    public function getPaymentParams()
    {
//        try {
//            $params = parent::getPaymentParams();
//
//            $params['extra']['KhbCardPocketId']=$this->scopeConfig->getValue('payment/bigfish_pmgw_khbszep/khbcardpocketid');
//
//			if (!strlen($params['extra']['KhbCardPocketId'])) {
//				throw new \Exception();
//			}
//
//            return $params;
//        } catch (Exception $e) {
//            throw new \Magento\Framework\Exception\LocalizedException($this->_getHelper()->__('validation_invalidPocketId'));
//        }
    }
}