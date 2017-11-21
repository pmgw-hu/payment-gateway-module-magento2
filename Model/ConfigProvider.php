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

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'bigfish_pmgw';

    const CODE_ABAQOOS = 'bigfish_pmgw_abaqoos';
    const CODE_BARION = 'bigfish_pmgw_barion';
    const CODE_BORGUN = 'bigfish_pmgw_borgun';
    const CODE_CIB = 'bigfish_pmgw_cib';
    const CODE_ESCALION = 'bigfish_pmgw_escalion';
    const CODE_FHB = 'bigfish_pmgw_fhb';
    const CODE_KHB = 'bigfish_pmgw_khb';
    const CODE_KHB_SZEP = 'bigfish_pmgw_khbszep';
    const CODE_MCM = 'bigfish_pmgw_mcm';
    const CODE_MKB_SZEP = 'bigfish_pmgw_mkbszep';
    const CODE_MPP = 'bigfish_pmgw_mpp';
    const CODE_OTP = 'bigfish_pmgw_otp';
    const CODE_OTP2 = 'bigfish_pmgw_otp2';
    const CODE_OTPAY = 'bigfish_pmgw_otpay';
    const CODE_OTPAY_MP = 'bigfish_pmgw_otpaymp';
    const CODE_OTP_MULTIPONT = 'bigfish_pmgw_otpmultipont';
    const CODE_OTP_SIMPLE = 'bigfish_pmgw_otpsimple';
    const CODE_OTP_SIMPLE_WIRE = 'bigfish_pmgw_otpsimplewire';
    const CODE_OTP_SZEP = 'bigfish_pmgw_otpszep';
    const CODE_PAYPAL = 'bigfish_pmgw_paypal';
    const CODE_PAYU = 'bigfish_pmgw_payu';
    const CODE_PAYU2 = 'bigfish_pmgw_payu2';
    const CODE_PAYU_CASH = 'bigfish_pmgw_payucash';
    const CODE_PAYU_MOBILE = 'bigfish_pmgw_payumobile';
    const CODE_PAYU_WIRE = 'bigfish_pmgw_payuwire';
    const CODE_SAFERPAY = 'bigfish_pmgw_saferpay';
    const CODE_SMS = 'bigfish_pmgw_sms';
    const CODE_SOFORT = 'bigfish_pmgw_sofort';
    const CODE_UNICREDIT = 'bigfish_pmgw_unicredit';
    const CODE_WIRECARD = 'bigfish_pmgw_wirecard';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $scopeConfig = $this->scopeConfig->getValue('payment');

        $methods = self::CODE . '_';

        $params = array_filter(
            $scopeConfig,
            function ($key) use ($methods) {
                return strpos($key, $methods) === 0;
            },
            ARRAY_FILTER_USE_KEY
        );

        $providers = array();

        foreach ($params as $key => $param) {
            if ($param['active']) {
                $providers[] = array_merge(array('name'=>$key), $scopeConfig[self::CODE], $param);
            }
        }

        $used_keys = array();

        foreach ($providers as $provider_key=>$provider) {
            foreach ($provider as $key=>$val) {
                $used_keys[$key] = $key;
            }
        }

        foreach ($providers as $provider_key=>&$provider) {
            foreach ($used_keys as $key=>$val) {
                if (!isset($provider[$key])) {
                    $provider[$key] = '';
                }
            }
        }

        return [
            'payment' => [
                self::CODE => [
                    'providers' => $providers
                ]
            ]
        ];
    }

    /**
     * PaymentGateway KHB Pocket Ids
     *
     * @return array
     */
    public function getKhbCardPocketId()
    {
        return array(
            '' => __('Please, select a pocket.'),
            '1' => __('Accommodation'),
            '2' => __('Hospitality'),
            '3' => __('Leisure')
        );
    }

    /**
     * PaymentGateway MKB Pocket Ids
     *
     * @return array
     */
    public function getMkbSzepCafeteriaId()
    {
        return array(
            '' => __('Please, select a pocket.'),
            '1111' => __('Accommodation'),
            '2222' => __('Hospitality'),
            '3333' => __('Leisure')
        );
    }

    /**
     * PaymentGateway OTP Pocket Ids
     *
     * @return array
     */
    public function getOtpCardPocketId()
    {
        return array(
            '' => __('Please, select a pocket.'),
            '09' => __('Accommodation'),
            '07' => __('Hospitality'),
            '08' => __('Leisure')
        );
    }

    /**
     * PaymentGateway Saferpay Payment Methods
     *
     * @return array
     */
    public function getSaferpayPaymentMethods()
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
     * PaymentGateway Saferpay Wallets
     *
     * @return array
     */
    public function getSaferpayWallets()
    {
        return array (
            array (
                'value' => 'MASTERPASS',
                'label' => __('saferpay_wallet_masterpass')
            )
        );
    }

    /**
     * PaymentGateway QPAY Payment Types
     *
     * @return array
     */
    public function getQpayPaymentTypes()
    {
        return array(
            'SELECT' => __('qpay_payment_type_select'),
            'BANCONTACT_MISTERCASH' => __('qpay_payment_type_bancontact_mistercash'),
            'CCARD' => __('qpay_payment_type_ccard'),
            'CCARD-MOTO' => __('qpay_payment_type_ccard_moto'),
            'EKONTO' => __('qpay_payment_type_ekonto'),
            'EPAY_BG' => __('qpay_payment_type_epay_bg'),
            'EPS' => __('qpay_payment_type_eps'),
            'GIROPAY' => __('qpay_payment_type_giropay'),
            'IDL' => __('qpay_payment_type_idl'),
            'MONETA' => __('qpay_payment_type_moneta'),
            'MPASS' => __('qpay_payment_type_mpass'),
            'PRZELEWY24' => __('qpay_payment_type_przelewy24'),
            'PAYPAL' => __('qpay_payment_type_paypal'),
            'PBX' => __('qpay_payment_type_pbx'),
            'POLI' => __('qpay_payment_type_poli'),
            'PSC' => __('qpay_payment_type_psc'),
            'QUICK' => __('qpay_payment_type_quick'),
            'SEPA-DD' => __('qpay_payment_type_sepa_dd'),
            'SKRILLDIRECT' => __('qpay_payment_type_skrilldirect'),
            'SKRILLWALLET' => __('qpay_payment_type_skrillwallet'),
            'SOFORTUEBERWEISUNG' => __('qpay_payment_type_sofortueberweisung'),
            'TATRAPAY' => __('qpay_payment_type_tatrapay'),
            'TRUSTLY' => __('qpay_payment_type_trustly'),
            'TRUSTPAY' => __('qpay_payment_type_trustpay'),
            'VOUCHER' => __('qpay_payment_type_voucher'),
        );
    }

}
