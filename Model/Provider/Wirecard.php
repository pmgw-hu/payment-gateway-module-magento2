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

class Wirecard extends Provider
{
    protected $_formBlockType = 'paymentgateway/form_wirecard';
    protected $_code  = 'bigfish_pmgw_wirecard';

    protected $_paymentMethod = 'QPAY';

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
    /**
     * Prepare params array to send it to gateway page via POST
     *
     * @return array
     */
    public function getPaymentParams()
    {
        try {
            $params = parent::getPaymentParams();

            $payment_data = $this->request->getParam('payment');

            $extra = array(
                'QpayPaymentType' => $payment_data[$this->_code]['payment_type']
            );
            $params['extra'] = $extra;

            return $params;
        } catch (Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException($this->_getHelper()->__('validation_invalidCcData'));
        }
    }

    /**
     * @return array
     */
    public function getPaymentTypes() {
        return array(
            'SELECT'				=> __('qpay_payment_type_select'),
			      'BANCONTACT_MISTERCASH'	=> __('qpay_payment_type_bancontact_mistercash'),
            'CCARD'					=> __('qpay_payment_type_ccard'),
            'CCARD-MOTO'			=> __('qpay_payment_type_ccard_moto'),
            'EKONTO'				=> __('qpay_payment_type_ekonto'),
            'EPAY_BG'				=> __('qpay_payment_type_epay_bg'),
            'EPS'					=> __('qpay_payment_type_eps'),
            'GIROPAY'				=> __('qpay_payment_type_giropay'),
            'IDL'					=> __('qpay_payment_type_idl'),
            'MONETA'				=> __('qpay_payment_type_moneta'),
            'MPASS'					=> __('qpay_payment_type_mpass'),
            'PRZELEWY24'			=> __('qpay_payment_type_przelewy24'),
            'PAYPAL'				=> __('qpay_payment_type_paypal'),
            'PBX'					=> __('qpay_payment_type_pbx'),
            'POLI'					=> __('qpay_payment_type_poli'),
            'PSC'					=> __('qpay_payment_type_psc'),
            'QUICK'					=> __('qpay_payment_type_quick'),
            'SEPA-DD'				=> __('qpay_payment_type_sepa_dd'),
            'SKRILLDIRECT'			=> __('qpay_payment_type_skrilldirect'),
            'SKRILLWALLET'			=> __('qpay_payment_type_skrillwallet'),
            'SOFORTUEBERWEISUNG'	=> __('qpay_payment_type_sofortueberweisung'),
            'TATRAPAY'				=> __('qpay_payment_type_tatrapay'),
            'TRUSTLY'				=> __('qpay_payment_type_trustly'),
            'TRUSTPAY'				=> __('qpay_payment_type_trustpay'),
            'VOUCHER'				=> __('qpay_payment_type_voucher'),
        );
    }

}