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

class Otpsimple extends Provider
{
    protected $_formBlockType = 'paymentgateway/form_otpsimple';
    protected $_code  = 'bigfish_pmgw_otpsimple';
    protected $_paymentMethod = 'OTPSimple';

//    /**
//     * @var \Magento\Framework\App\Request\Http
//     */
//    protected $request;
//
//    public function __construct(
//        \Magento\Framework\App\Request\Http $request
//    ) {
//        $this->request = $request;
//    }
//    /**
//	 * Prepare params array to send it to gateway page via POST
//	 *
//	 * @return array
//	 */
//	public function getPaymentParams()
//	{
//		try {
//			$params = parent::getPaymentParams();
//
//			$payment_data = $this->request->getParam('payment');
//			$params["OneClickPayment"] = $payment_data[$this->_code]['one_click_payment'];
//
//			return $params;
//		} catch (Exception $e) {
//			throw new \Magento\Framework\Exception\LocalizedException($this->_getHelper()->__('validation_invalidCcData'));
//		}
//	}
}