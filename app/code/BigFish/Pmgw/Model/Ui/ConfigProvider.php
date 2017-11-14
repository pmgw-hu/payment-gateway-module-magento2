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
namespace BigFish\Pmgw\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use BigFish\Pmgw\Model\Provider;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'bigfish_pmgw';

    const ABAQOOS_CODE          = 'bigfish_pmgw_abaqoos';
    const BARION_CODE           = 'bigfish_pmgw_barion';
    const BORGUN_CODE           = 'bigfish_pmgw_borgun';
    const CIB_CODE              = 'bigfish_pmgw_cib';
    const ESCALION_CODE         = 'bigfish_pmgw_escalion';
    const FHB_CODE              = 'bigfish_pmgw_fhb';
    const KHB_CODE              = 'bigfish_pmgw_khb';
    const KHBSZEP_CODE          = 'bigfish_pmgw_khbszep';
    const MCM_CODE              = 'bigfish_pmgw_mcm';
    const MKBSZEP_CODE          = 'bigfish_pmgw_mkbszep';
    const MPP_CODE              = 'bigfish_pmgw_mpp';
    const OTP_CODE              = 'bigfish_pmgw_otp';
    const OTP2_CODE             = 'bigfish_pmgw_otp2';
    const OTPAY_CODE            = 'bigfish_pmgw_otpay';
    const OTPAYMP_CODE          = 'bigfish_pmgw_otpaymp';
    const OTPMULTIPONT_CODE     = 'bigfish_pmgw_otpmultipont';
    const OTPSIMPLE_CODE        = 'bigfish_pmgw_otpsimple';
    const OTPSIMPLEWIRE_CODE    = 'bigfish_pmgw_otpsimplewire';
    const OTPSZEP_CODE          = 'bigfish_pmgw_otpszep';
    const PAYPAL_CODE           = 'bigfish_pmgw_paypal';
    const PAYU_CODE             = 'bigfish_pmgw_payu';
    const PAYU2_CODE            = 'bigfish_pmgw_payu2';
    const PAYUCASH_CODE         = 'bigfish_pmgw_payucash';
    const PAYUMOBILE_CODE       = 'bigfish_pmgw_payumobile';
    const PAYUWIRE_CODE         = 'bigfish_pmgw_payuwire';
    const SAFERPAY_CODE         = 'bigfish_pmgw_saferpay';
    const SMS_CODE              = 'bigfish_pmgw_sms';
    const SOFORT_CODE           = 'bigfish_pmgw_sofort';
    const UNICREDIT_CODE        = 'bigfish_pmgw_unicredit';
    const WIRECARD_CODE         = 'bigfish_pmgw_wirecard';

    /**
     * @var \BigFish\Pmgw\Model\Provider\Saferpay
     */
    protected $paymentGatewaySaferpayFactory;

    /**
     * @var \BigFish\Pmgw\Model\Provider\Wirecard
     */
    protected $paymentGatewayWirecardFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $config;

    /**
     * ConfigProvider constructor.
     *
     * @param \BigFish\Pmgw\Model\Provider\Saferpay $paymentGatewaySaferpayFactory
     * @param \BigFish\Pmgw\Model\Provider\Wirecard $paymentGatewayWirecardFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Provider\Saferpay $paymentGatewaySaferpayFactory,
        Provider\Wirecard $paymentGatewayWirecardFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->paymentGatewaySaferpayFactory = $paymentGatewaySaferpayFactory;
        $this->paymentGatewayWirecardFactory = $paymentGatewayWirecardFactory;
        $this->scopeConfig = $scopeConfig;
    }
    /**
     * Retrieve assoc array of checkout configuration
     *
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


        $this->config = [
            'payment' => [
                self::CODE => [
                    'providers' => $providers
                ]
            ]
        ];

        return $this->config;
    }

    /**
     * PaymentGateway USE API
     *
     * @return array
     */
    public function getApiType()
    {
        return array(
            'REST' => __('HTTP REST API (Default)'),
        );
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
        $saferpay = $this->paymentGatewaySaferpayFactory;
        return $saferpay->getPaymentMethods();
    }

    /**
     * PaymentGateway Saferpay Wallets
     *
     * @return array
     */
    public function getSaferpayWallets()
    {
        $saferpay = $this->paymentGatewaySaferpayFactory;
        return $saferpay->getWallets();
    }

    /**
     * PaymentGateway QPAY Payment Types
     *
     * @return array
     */
    public function getQpayPaymentTypes()
    {
        $qpay = $this->paymentGatewayWirecardFactory;
        return $qpay->getPaymentTypes();
    }
}
