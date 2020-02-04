<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      BIG FISH Payment Gateway module for Magento 2
 * @category   BigFish
 * @package    Bigfishpaymentgateway_Pmgw
 * @author     BIG FISH Ltd., paymentgateway [at] bigfish [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */
namespace Bigfishpaymentgateway\Pmgw\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'bigfishpaymentgateway_pmgw';

    const CODE_BARION2 = 'bigfishpaymentgateway_pmgw_barion2';
    const CODE_BBARUHITEL = 'bigfishpaymentgateway_pmgw_bbaruhitel';
    const CODE_BORGUN = 'bigfishpaymentgateway_pmgw_borgun';
    const CODE_BORGUN2 = 'bigfishpaymentgateway_pmgw_borgun2';
    const CODE_CIB = 'bigfishpaymentgateway_pmgw_cib';
    const CODE_ESCALION = 'bigfishpaymentgateway_pmgw_escalion';
    const CODE_FHB = 'bigfishpaymentgateway_pmgw_fhb';
    const CODE_GP = 'bigfishpaymentgateway_pmgw_gp';
    const CODE_IPG = 'bigfishpaymentgateway_pmgw_ipg';
    const CODE_KHB = 'bigfishpaymentgateway_pmgw_khb';
    const CODE_KHB_SZEP = 'bigfishpaymentgateway_pmgw_khbszep';
    const CODE_MKB_SZEP = 'bigfishpaymentgateway_pmgw_mkbszep';
    const CODE_OTP = 'bigfishpaymentgateway_pmgw_otp';
    const CODE_OTPARUHITEL = 'bigfishpaymentgateway_pmgw_otparuhitel';
    const CODE_OTPAY_MP = 'bigfishpaymentgateway_pmgw_otpaymp';
    const CODE_OTP_SIMPLE = 'bigfishpaymentgateway_pmgw_otpsimple';
    const CODE_OTP_SIMPLE_WIRE = 'bigfishpaymentgateway_pmgw_otpsimplewire';
    const CODE_OTP_SZEP = 'bigfishpaymentgateway_pmgw_otpszep';
    const CODE_PAYPAL = 'bigfishpaymentgateway_pmgw_paypal';
    const CODE_PAYSAFECARD = 'bigfishpaymentgateway_pmgw_paysafecard';
    const CODE_PAYSAFECASH = 'bigfishpaymentgateway_pmgw_paysafecash';
    const CODE_PAYU2 = 'bigfishpaymentgateway_pmgw_payu2';
    const CODE_SAFERPAY = 'bigfishpaymentgateway_pmgw_saferpay';
    const CODE_SOFORT = 'bigfishpaymentgateway_pmgw_sofort';
    const CODE_UNICREDIT = 'bigfishpaymentgateway_pmgw_unicredit';
    const CODE_VIRPAY = 'bigfishpaymentgateway_pmgw_virpay';
    const CODE_WIRECARD = 'bigfishpaymentgateway_pmgw_wirecard';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $providers = $this->getProviders($this->scopeConfig->getValue(
            'payment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        ));

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
        $scopeConfig = $this->scopeConfig->getValue(
            'payment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
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

    /**
     * @return array
     */
    public function getCardRegistrationModes()
    {
        return [
            '0' => __('No'),
            '1' => __('Only card registration'),
            '2' => __('Card registration and One Click Payment'),
        ];
    }
}
