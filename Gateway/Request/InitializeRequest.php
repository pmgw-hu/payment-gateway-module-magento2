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
use BigFish\Pmgw\Model\Ui\ConfigProvider;
use BigFish\Pmgw\Gateway\Helper\Helper;
use BigFish\PaymentGateway;

use BigFish\Pmgw\Model\TransactionFactory;
use BigFish\Pmgw\Model\LogFactory;

/**
 * Class InitializeRequest
 *
 * @package BigFish\Pmgw\Gateway\Request
 */
class InitializeRequest implements BuilderInterface
{

    const MODULE_NAME = 'BigFish_Pmgw';

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var \BigFish\Pmgw\Model\Ui\ConfigProvider
     */
    private $providerConfig;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $_store;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $_moduleList;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \BigFish\Pmgw\Model\TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var \BigFish\Pmgw\Model\LogFactory
     */
    private $logFactory;

    /**
     * @param \Magento\Payment\Gateway\ConfigInterface $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \BigFish\Pmgw\Model\Ui\ConfigProvider $providerConfig
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \BigFish\Pmgw\Model\TransactionFactory $transactionFactory
     * @param \BigFish\Pmgw\Model\LogFactory $logFactory
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
        $this->_scopeConfig = $scopeConfig;
        $this->providerConfig = $providerConfig;
        $this->_store = $store;
        $this->_moduleList = $moduleList;
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
        $address = $order->getShippingAddress();

        $method = $payment->getPayment()->getMethodInstance();

        $params = $this->_scopeConfig->getValue('payment/bigfish_pmgw');

        $methodCode = $method->getCode();

        $paymentParams = $this->_scopeConfig->getValue('payment/' . $methodCode);

        if ($paymentParams['provider_code'] == 'OTPSZEP') {
            $storeName = $paymentParams['storenameotpszep'];
            $apiKey = $paymentParams['apikeyotpszep'];
        } else {
            $storeName = $params['storename'];
            $apiKey = $params['apikey'];
        }

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
            ->setProviderName(($paymentParams['provider_code'] == 'OTPSZEP') ? PaymentGateway::PROVIDER_OTP : $paymentParams['provider_code'])
            ->setResponseUrl($baseUrl . $paymentParams['responseUrl'])
            ->setAmount($order->getGrandTotalAmount())
            ->setCurrency($paymentParams['currency'])
            ->setOrderId($order->getOrderIncrementId())
            ->setUserId($order->getCustomerId())
            ->setLanguage('HU')
            ->setMppPhoneNumber(isset($paymentParams['mppPhoneNumber']) ? $paymentParams['mppPhoneNumber'] : '')
            ->setOtpCardNumber(isset($paymentParams['OtpCardNumber']) ? $paymentParams['OtpCardNumber'] : '')
            ->setOtpExpiration(isset($paymentParams['OtpExpiration']) ? $paymentParams['OtpExpiration'] : '')
            ->setOtpCvc(isset($paymentParams['OtpCvc']) ? $paymentParams['OtpCvc'] : '')
            ->setOneClickPayment(isset($paymentParams['OneClickPayment']) ? $paymentParams['OneClickPayment'] : '')
            ->setModuleName('Magento (' . $magentoVersion . ')')
            ->setModuleVersion($this->_moduleList->getOne(self::MODULE_NAME)['setup_version']);

        // $request->setAutoCommit(false);

        if ($paymentParams['provider_code'] == 'OTPSZEP') {
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

//            $response->{'orderId'} = $orderId;

            $pmgwFactory = $this->transactionFactory->create();
            $pmgwFactory->setOrderId($orderId)
                ->setTransactionId($response->TransactionId)
                ->setCreatedTime(date("Y-m-d H:i:s"))
                ->setStatus(Helper::TRANSACTION_STATUS_INITED)
                ->save();

            $pmgw_id = $pmgwFactory->getId();

            $pmgwLogFactory = $this->transactionFactory->create();
            $pmgwLogFactory->setPaymentgatewayId($pmgw_id)
                ->setOrderId($orderId)
                ->setTransactionId($response->TransactionId)
                ->setCreatedTime(date("Y-m-d H:i:s"))
                ->setStatus(Helper::TRANSACTION_STATUS_INITED)
                ->save();

        } else {

            $errorMessage = "PAYMENT_PARAMS:\n".print_r($paymentParams, true)."\n\n";
            $errorMessage.= $response->ResultCode.": ".$response->ResultMessage;
            $errorMessage.= "<br/><br/><xmp>".print_r($response, true)."</xmp>";

            throw new \UnexpectedValueException($errorMessage);
        }

        // Convert to array because of transferBuilder (TransferFactory class)
        return (array)$response;
    }
}
