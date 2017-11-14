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


use Magento\Framework\ObjectManager\ObjectManager;

class Saferpay extends Provider
{
	protected $_formBlockType = 'paymentgateway/form_saferpay';

    protected $_code  = 'bigfish_pmgw_saferpay';

    protected $_paymentMethod = 'Saferpay';

//    /**
//     * @var \Magento\Framework\App\Request\Http
//     */
//    protected $request;
//
//    public function __construct(
//        ObjectManager $request
//    ) {
//        $this->request = $request;
//    }

    /**
	 * Prepare params array to send it to gateway page via POST
	 *
	 * @return array
	 */
	public function getPaymentParams()
	{
//		try {
//			$params = parent::getPaymentParams();
//
//			$payment_data = $this->getOrder()->getParam('payment');
//			$params['OneClickPayment'] = $payment_data[$this->_code]['one_click_payment'];
//
//			$extra = array(
//				'SaferpayPaymentMethods' => $payment_data[$this->_code]['payment_methods'],
//				'SaferpayWallets' => $payment_data[$this->_code]['wallets']
//			);
//			$params['extra'] = $extra;
//
//			return $params;
//		} catch (Exception $e) {
//			throw new \Magento\Framework\Exception\LocalizedException($this->_getHelper()->__('validation_invalidCcData'));
//		}
	}

	/**
	 * Get Saferpay payment methods
	 * @return array
	 */
	public function getPaymentMethods()
	{
		return array (
			array (
				'value' => 'AMEX',
				'label' => __('saferpay_payment_method_amex')
			),
			array (
				'value' => 'DIRECTDEBIT',
				'label' => __('saferpay_payment_method_directdebit')
			),
			array (
				'value' => 'INVOICE',
				'label' => __('saferpay_payment_method_invoice')
			),
			array (
				'value' => 'BONUS',
				'label' => __('saferpay_payment_method_bonus')
			),
			array (
				'value' => 'DINERS',
				'label' => __('saferpay_payment_method_diners')
			),
			array (
				'value' => 'EPRZELEWY',
				'label' => __('saferpay_payment_method_eprzelewy')
			),
			array (
				'value' => 'EPS',
				'label' => __('saferpay_payment_method_eps')
			),
			array (
				'value' => 'GIROPAY',
				'label' => __('saferpay_payment_method_giropay')
			),
			array (
				'value' => 'IDEAL',
				'label' => __('saferpay_payment_method_ideal')
			),
			array (
				'value' => 'JCB',
				'label' => __('saferpay_payment_method_jcb')),
			array (
				'value' => 'MAESTRO',
				'label' => __('saferpay_payment_method_maestro')
			),
			array (
				'value' => 'MASTERCARD',
				'label' => __('saferpay_payment_method_mastercard')
			),
			array (
				'value' => 'MYONE',
				'label' => __('saferpay_payment_method_myone')
			),
			array (
				'value' => 'PAYPAL',
				'label' => __('saferpay_payment_method_paypal')
			),
			array (
				'value' => 'POSTCARD',
				'label' => __('saferpay_payment_method_postcard')
			),
			array (
				'value' => 'POSTFINANCE',
				'label' => __('saferpay_payment_method_postfinance')
			),
			array (
				'value' => 'SAFERPAYTEST',
				'label' => __('saferpay_payment_method_saferpaytest')
			),
			array (
				'value' => 'SOFORT',
				'label' => __('saferpay_payment_method_sofort')
			),
			array (
				'value' => 'VISA',
				'label' => __('saferpay_payment_method_visa')
			),
			array (
				'value' => 'VPAY',
				'label' => __('saferpay_payment_method_vpay')
			),
		);
	}

	/**
	 * Get Saferpay wallets
	 * @return array
	 */
	public function getWallets()
	{
		return array (
			array (
				'value' => 'MASTERPASS',
				'label' => __('saferpay_wallet_masterpass')
			)
		);
	}

}