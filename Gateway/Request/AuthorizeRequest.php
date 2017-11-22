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
namespace BigFish\Pmgw\Gateway\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Backend\Model\UrlInterface;
use BigFish\Pmgw\Model\ConfigProvider;
use BigFish\Pmgw\Model\TransactionFactory;
use BigFish\Pmgw\Model\LogFactory;
use BigFish\Pmgw\Gateway\Helper\Helper;
use BigFish\PaymentGateway;

class AuthorizeRequest implements BuilderInterface
{
    const MODULE_NAME = 'BigFish_Pmgw';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigProvider
     */
    private $providerConfig;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @param ConfigInterface $config
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigProvider $providerConfig
     * @param StoreInterface $store
     * @param ModuleListInterface $moduleList
     * @param OrderFactory $orderFactory
     * @param TransactionFactory $transactionFactory
     * @param LogFactory $logFactory
     */
    public function __construct(
        ConfigInterface $config,
        ScopeConfigInterface $scopeConfig,
        ConfigProvider $providerConfig,
        StoreInterface $store,
        ModuleListInterface $moduleList,
        OrderFactory $orderFactory,
        TransactionFactory $transactionFactory,
        LogFactory $logFactory
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->providerConfig = $providerConfig;
        $this->store = $store;
        $this->moduleList = $moduleList;
        $this->orderFactory = $orderFactory;
        $this->transactionFactory = $transactionFactory;
        $this->logFactory = $logFactory;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {

        if (
            !isset($buildSubject['payment']) ||
            !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        //$address = $order->getShippingAddress();

        $method = $payment->getPayment()->getMethodInstance();

        $params = $this->scopeConfig->getValue('payment/bigfish_pmgw');

        $methodCode = $method->getCode();

        //$paymentParams = $this->scopeConfig->getValue('payment/' . $methodCode);

        //if ($paymentParams['provider_code'] == 'OTPSZEP') {
        //if ($methodCode == ConfigProvider::CODE_OTP_SZEP) {
        //    $storeName = $paymentParams['storenameotpszep'];
        //    $apiKey = $paymentParams['apikeyotpszep'];
        //} else {
        $storeName = $params['storename'];
        $apiKey = $params['apikey'];
        //}

        $config = new PaymentGateway\Config();

        $config->storeName = $storeName;
        $config->apiKey = $apiKey;
        if (isset($params['publickey'])) {
            $config->encryptPublicKey = $params['publickey'];
        }
        $config->testMode = $params['testmode'] == 1;
        $config->outCharset = 'UTF-8';

        /**
         * Configure PaymentGateway
         */
        PaymentGateway::setConfig($config);

        $paymentConfig = $this->providerConfig->getConfig();

        $paymentParams = array();

        foreach ($paymentConfig['payment']['bigfish_pmgw']['providers'] as $value) {
            if ($value['name'] === $methodCode) {
                $paymentParams = $value;
                break;
            }
        }

        if (empty($paymentParams)) {
            throw new \UnexpectedValueException('Payment parameter array should be provided');
        }

        $objectManager = ObjectManager::getInstance();

        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $magentoVersion = $productMetadata->getVersion();

        $orderId = $order->getOrderIncrementId();

        $request = new PaymentGateway\Request\Init();

        $request
            ->setProviderName($paymentParams['provider_code'])
            ->setResponseUrl($baseUrl . $paymentParams['responseUrl'])
            ->setAmount($order->getGrandTotalAmount())
            ->setCurrency($order->getCurrencyCode())
            ->setOrderId($order->getOrderIncrementId())
            ->setUserId($order->getCustomerId())
            ->setLanguage('HU')
            ->setMppPhoneNumber(isset($paymentParams['mppPhoneNumber']) ? $paymentParams['mppPhoneNumber'] : '')
            ->setOtpCardNumber(isset($paymentParams['OtpCardNumber']) ? $paymentParams['OtpCardNumber'] : '')
            ->setOtpExpiration(isset($paymentParams['OtpExpiration']) ? $paymentParams['OtpExpiration'] : '')
            ->setOtpCvc(isset($paymentParams['OtpCvc']) ? $paymentParams['OtpCvc'] : '')
            ->setOneClickPayment(isset($paymentParams['OneClickPayment']) ? $paymentParams['OneClickPayment'] : '')
            ->setModuleName('Magento (' . $magentoVersion . ')')
            ->setModuleVersion($this->moduleList->getOne(self::MODULE_NAME)['setup_version']);

        if ($methodCode == ConfigProvider::CODE_OTP_SZEP) {
            $request->setOtpCardPocketId(isset($paymentParams['otpcardpocketid']) ? $paymentParams['otpcardpocketid'] : '');
        }

        // TODO: lehet hogy kivesszuk:
        if ($paymentParams['provider_code'] == PaymentGateway::PROVIDER_OTP_TWO_PARTY) {
            $paymentParams['OtpCardNumber'] = '****************';
            $paymentParams['OtpExpiration'] = '****';
            $paymentParams['OtpCvc'] = '***';
        }

        if ($paymentParams['provider_code'] == PaymentGateway::PROVIDER_MKB_SZEP) {
            $request
                ->setMkbSzepCafeteriaId(isset($paymentParams['mkbszepcafeteriaid']) ? $paymentParams['mkbszepcafeteriaid'] : '')
                ->setGatewayPaymentPage(TRUE);
        }

        if ($paymentParams['provider_code'] === PaymentGateway::PROVIDER_KHB_SZEP &&
            isset($paymentParams[PaymentGateway::PROVIDER_KHB_SZEP]['khbcardpocketid'])
        ) {
            $extra['khbcardpocketid'] = isset($paymentParams[PaymentGateway::PROVIDER_KHB_SZEP]['khbcardpocketid']);
        }

        if (isset($paymentParams['extra']) && is_array($paymentParams['extra']) && !empty($paymentParams['extra'])) {
            $request->setExtra($paymentParams['extra']);
        }

        /**
         * Init PaymentGateway request
         */
        $response = PaymentGateway::init($request);

        if ($response->ResultCode === PaymentGateway::RESULT_CODE_SUCCESS) {
            $transactionFactory = $this->transactionFactory->create();
            $transactionFactory->setOrderId($orderId)
                ->setTransactionId($response->TransactionId)
                ->setCreatedTime(date("Y-m-d H:i:s"))
                ->setStatus(Helper::TRANSACTION_STATUS_INITIALIZED)
                ->save();

            $transactionId = $transactionFactory->getId();

            $pmgwLogFactory = $this->logFactory->create();
            $pmgwLogFactory->setPaymentgatewayId($transactionId)
                ->setOrderId($orderId)
                ->setTransactionId($response->TransactionId)
                ->setCreatedTime(date("Y-m-d H:i:s"))
                ->setStatus(Helper::TRANSACTION_STATUS_INITIALIZED)
                ->setDebug(print_r($response, true))
                ->save();

        } else {
            $errorMessage = "PAYMENT_PARAMS:\n".print_r($paymentParams, true)."\n\n";
            $errorMessage.= $response->ResultCode.": ".$response->ResultMessage;
            $errorMessage.= "<br/><br/><xmp>".print_r($response, true)."</xmp>";

            throw new \UnexpectedValueException($errorMessage);
        }

        return (array)$response;
    }

}
