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
    const CODE_PAYSAFECARD = 'bigfish_pmgw_paysafecard';
    const CODE_PAYSAFECASH = 'bigfish_pmgw_paysafecash';
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
                return array_merge($this->getCommonConfig(), $providerConfig);
            }
        }
        return [];
    }

    /**
     * @return array
     */
    protected function getCommonConfig()
    {
        $scopeConfig = $this->scopeConfig->getValue('payment');
        return (array)$scopeConfig[self::CODE];
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
            function ($key, array $data) {
                $data['name'] = $key;
                return $data;
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
                'label' => __('American Express'),
            ],
            [
                'value' => 'DIRECTDEBIT',
                'label' => __('Direct Debit'),
            ],
            [
                'value' => 'INVOICE',
                'label' => __('Invoice'),
            ],
            [
                'value' => 'BONUS',
                'label' => __('Bonus')
            ],
            [
                'value' => 'DINERS',
                'label' => __('Diners')
            ],
            [
                'value' => 'EPRZELEWY',
                'label' => __('ePrzelewy')
            ],
            [
                'value' => 'EPS',
                'label' => __('eps Online-wire')
            ],
            [
                'value' => 'GIROPAY',
                'label' => __('giropay')
            ],
            [
                'value' => 'IDEAL',
                'label' => __('iDEAL')
            ],
            [
                'value' => 'JCB',
                'label' => __('JCB')
            ],
            [
                'value' => 'MAESTRO',
                'label' => __('Maestro')
            ],
            [
                'value' => 'MASTERCARD',
                'label' => __('MasterCard')
            ],
            [
                'value' => 'MYONE',
                'label' => __('My One')
            ],
            [
                'value' => 'PAYPAL',
                'label' => __('PayPal')
            ],
            [
                'value' => 'POSTCARD',
                'label' => __('PostFinance Card')
            ],
            [
                'value' => 'POSTFINANCE',
                'label' => __('PostFinance')
            ],
            [
                'value' => 'SAFERPAYTEST',
                'label' => __('Saferpay test')
            ],
            [
                'value' => 'SOFORT',
                'label' => __('SOFORT Banking')
            ],
            [
                'value' => 'VISA',
                'label' => __('Visa')
            ],
            [
                'value' => 'VPAY',
                'label' => __('V PAY')
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
                'label' => __('MasterPass'),
            ],
        ];
    }

    /**
     * @return array
     */
    public function getWirecardPaymentTypes()
    {
        return [
            'SELECT' => __('Select it on Wirecard side'),
            'BANCONTACT_MISTERCASH' => __('Bancontact/Mister Cash'),
            'CCARD' => __('Credit Card Maestro SecureCode'),
            'CCARD-MOTO' => __('Credit Card - Mail Order and Telephone Order'),
            'EKONTO' => __('eKonto'),
            'EPAY_BG' => __('ePay.bg'),
            'EPS' => __('eps Online-wire'),
            'GIROPAY' => __('giropay'),
            'IDL' => __('iDEAL'),
            'MONETA' => __('moneta.ru'),
            'MPASS' => __('mpass'),
            'PRZELEWY24' => __('Przelewy24'),
            'PAYPAL' => __('PayPal'),
            'PBX' => __('paybox'),
            'POLI' => __('POLi'),
            'PSC' => __('paysafecard'),
            'QUICK' => __('@Quick'),
            'SEPA-DD' => __('SEPA Direct Debit'),
            'SKRILLDIRECT' => __('Skrill Direct'),
            'SKRILLWALLET' => __('Skrill Digital Wallet'),
            'SOFORTUEBERWEISUNG' => __('SOFORT Banking'),
            'TATRAPAY' => __('TatraPay'),
            'TRUSTLY' => __('Trustly'),
            'TRUSTPAY' => __('TrustPay'),
            'VOUCHER' => __('My Voucher'),
        ];
    }

}
