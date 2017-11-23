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

    const CODE_BARION2 = 'bigfish_pmgw_barion2';
    const CODE_BORGUN = 'bigfish_pmgw_borgun';
    const CODE_BORGUN2 = 'bigfish_pmgw_borgun2';
    const CODE_CIB = 'bigfish_pmgw_cib';
    const CODE_ESCALION = 'bigfish_pmgw_escalion';
    const CODE_FHB = 'bigfish_pmgw_fhb';
    const CODE_GP = 'bigfish_pmgw_gp';
    const CODE_IPG = 'bigfish_pmgw_ipg';
    const CODE_KHB = 'bigfish_pmgw_khb';
    const CODE_KHB_SZEP = 'bigfish_pmgw_khbszep';
    const CODE_MKB_SZEP = 'bigfish_pmgw_mkbszep';
    const CODE_OTP = 'bigfish_pmgw_otp';
    const CODE_OTPAY_MP = 'bigfish_pmgw_otpaymp';
    const CODE_OTP_SIMPLE = 'bigfish_pmgw_otpsimple';
    const CODE_OTP_SZEP = 'bigfish_pmgw_otpszep';
    const CODE_PAYPAL = 'bigfish_pmgw_paypal';
    const CODE_PAYU2 = 'bigfish_pmgw_payu2';
    const CODE_SAFERPAY = 'bigfish_pmgw_saferpay';
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
        $providers = $this->getProviders($this->scopeConfig->getValue('payment'));

        $this->unifyProviderConfig($providers);

        return [
            'payment' => [
                self::CODE => [
                    'providers' => $providers,
                ]
            ]
        ];
    }

    /**
     * @param $code
     * @return array
     */
    public function getProviderConfig($code)
    {
        $config = $this->getConfig();

        foreach ($config['payment'][self::CODE]['providers'] as $providerConfig) {
            if ($providerConfig['name'] === $code) {
                return $providerConfig;
            }
        }
        return [];
    }

    /**
     * @param array $scopeConfig
     * @return array
     */
    protected function getProviders(array $scopeConfig)
    {
        $prefix = self::CODE . '_';

        $params = array_filter(
            $scopeConfig,
            function (array $data, $key) use ($prefix) {
                return (strpos($key, $prefix) === 0 && (int)$data['active']);
            },
            ARRAY_FILTER_USE_BOTH
        );

        $providers = array_map(
            function ($key, array $data) use ($scopeConfig) {
                return array_merge(
                    [
                        'name' => $key
                    ],
                    $scopeConfig[self::CODE],
                    $data
                );
            }, array_keys($params), $params);

        return $providers;
    }

    /**
     * @param array $providers
     */
    protected function unifyProviderConfig(array &$providers)
    {
        $keys = $this->collectProviderConfigKeys($providers);

        array_walk($providers, function (array &$provider) use ($keys) {
            $provider = array_merge($keys, $provider);
        });
    }

    /**
     * @param array $providers
     * @return array
     */
    protected function collectProviderConfigKeys(array $providers)
    {
        $keys = [];

        array_walk($providers, function (array $provider) use (&$keys) {
            foreach (array_keys($provider) as $key) {
                if (array_key_exists($key, $keys)) {
                    continue;
                }
                $keys[$key] = null;
            }
        });
        return $keys;
    }

    /**
     * @return array
     */
    public function getKhbCardPocketId()
    {
        return [
            '' => __('Please, select a pocket.'),
            '1' => __('Accommodation'),
            '2' => __('Hospitality'),
            '3' => __('Leisure'),
        ];
    }

    /**
     * @return array
     */
    public function getMkbCardPocketId()
    {
        return [
            '' => __('Please, select a pocket.'),
            '1111' => __('Accommodation'),
            '2222' => __('Hospitality'),
            '3333' => __('Leisure'),
        ];
    }

    /**
     * @return array
     */
    public function getOtpCardPocketId()
    {
        return [
            '' => __('Please, select a pocket.'),
            '09' => __('Accommodation'),
            '07' => __('Hospitality'),
            '08' => __('Leisure'),
        ];
    }

    /**
     * PaymentGateway Saferpay Payment Methods
     *
     * @return array
     */
    public function getSaferpayPaymentMethods()
    {
        return [
            [
                'value' => 'AMEX',
                'label' => __('saferpay_payment_method_amex')
            ],
            [
                'value' => 'DIRECTDEBIT',
                'label' => __('saferpay_payment_method_directdebit')
            ],
            [
                'value' => 'INVOICE',
                'label' => __('saferpay_payment_method_invoice')
            ],
            [
                'value' => 'BONUS',
                'label' => __('saferpay_payment_method_bonus')
            ],
            [
                'value' => 'DINERS',
                'label' => __('saferpay_payment_method_diners')
            ],
            [
                'value' => 'EPRZELEWY',
                'label' => __('saferpay_payment_method_eprzelewy')
            ],
            [
                'value' => 'EPS',
                'label' => __('saferpay_payment_method_eps')
            ],
            [
                'value' => 'GIROPAY',
                'label' => __('saferpay_payment_method_giropay')
            ],
            [
                'value' => 'IDEAL',
                'label' => __('saferpay_payment_method_ideal')
            ],
            [
                'value' => 'JCB',
                'label' => __('saferpay_payment_method_jcb')
            ],
            [
                'value' => 'MAESTRO',
                'label' => __('saferpay_payment_method_maestro')
            ],
            [
                'value' => 'MASTERCARD',
                'label' => __('saferpay_payment_method_mastercard')
            ],
            [
                'value' => 'MYONE',
                'label' => __('saferpay_payment_method_myone')
            ],
            [
                'value' => 'PAYPAL',
                'label' => __('saferpay_payment_method_paypal')
            ],
            [
                'value' => 'POSTCARD',
                'label' => __('saferpay_payment_method_postcard')
            ],
            [
                'value' => 'POSTFINANCE',
                'label' => __('saferpay_payment_method_postfinance')
            ],
            [
                'value' => 'SAFERPAYTEST',
                'label' => __('saferpay_payment_method_saferpaytest')
            ],
            [
                'value' => 'SOFORT',
                'label' => __('saferpay_payment_method_sofort')
            ],
            [
                'value' => 'VISA',
                'label' => __('saferpay_payment_method_visa')
            ],
            [
                'value' => 'VPAY',
                'label' => __('saferpay_payment_method_vpay')
            ],
        ];
    }

    /**
     * PaymentGateway Saferpay Wallets
     *
     * @return array
     */
    public function getSaferpayWallets()
    {
        return [
            [
                'value' => 'MASTERPASS',
                'label' => __('saferpay_wallet_masterpass')
            ],
        ];
    }

    /**
     * @return array
     */
    public function getWirecardPaymentTypes()
    {
        return [
            'SELECT' => __('wirecard_payment_type_select'),
            'BANCONTACT_MISTERCASH' => __('wirecard_payment_type_bancontact_mistercash'),
            'CCARD' => __('wirecard_payment_type_ccard'),
            'CCARD-MOTO' => __('wirecard_payment_type_ccard_moto'),
            'EKONTO' => __('wirecard_payment_type_ekonto'),
            'EPAY_BG' => __('wirecard_payment_type_epay_bg'),
            'EPS' => __('wirecard_payment_type_eps'),
            'GIROPAY' => __('wirecard_payment_type_giropay'),
            'IDL' => __('wirecard_payment_type_idl'),
            'MONETA' => __('wirecard_payment_type_moneta'),
            'MPASS' => __('wirecard_payment_type_mpass'),
            'PRZELEWY24' => __('wirecard_payment_type_przelewy24'),
            'PAYPAL' => __('wirecard_payment_type_paypal'),
            'PBX' => __('wirecard_payment_type_pbx'),
            'POLI' => __('wirecard_payment_type_poli'),
            'PSC' => __('wirecard_payment_type_psc'),
            'QUICK' => __('wirecard_payment_type_quick'),
            'SEPA-DD' => __('wirecard_payment_type_sepa_dd'),
            'SKRILLDIRECT' => __('wirecard_payment_type_skrilldirect'),
            'SKRILLWALLET' => __('wirecard_payment_type_skrillwallet'),
            'SOFORTUEBERWEISUNG' => __('wirecard_payment_type_sofortueberweisung'),
            'TATRAPAY' => __('wirecard_payment_type_tatrapay'),
            'TRUSTLY' => __('wirecard_payment_type_trustly'),
            'TRUSTPAY' => __('wirecard_payment_type_trustpay'),
            'VOUCHER' => __('wirecard_payment_type_voucher'),
        ];
    }

}
