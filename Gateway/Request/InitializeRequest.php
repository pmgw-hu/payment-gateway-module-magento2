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

use Magento\Framework\Module\ModuleListInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Backend\Model\UrlInterface;
use BigFish\Pmgw\Model\ConfigProvider;
use BigFish\Pmgw\Gateway\Helper\Helper;
use BigFish\PaymentGateway;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use BigFish\PaymentGateway\Config;
use BigFish\PaymentGateway\Request\Init as InitRequest;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Stdlib\DateTime\DateTime;

class InitializeRequest implements BuilderInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ConfigProvider
     */
    private $providerConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param Logger $logger
     * @param ConfigProvider $providerConfig
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param ModuleListInterface $moduleList
     * @param Helper $helper
     * @param DateTime $dateTime
     */
    public function __construct(
        Logger $logger,
        ConfigProvider $providerConfig,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata,
        ModuleListInterface $moduleList,
        Helper $helper,
        DateTime $dateTime
    ) {
        $this->logger = $logger;
        $this->providerConfig = $providerConfig;
        $this->storeManager = $storeManager;
        $this->productMetaData = $productMetadata;
        $this->moduleList = $moduleList;
        $this->helper = $helper;
        $this->dateTime = $dateTime;
    }

    /**
     * @param array $buildSubject
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

        /** @var OrderAdapterInterface $order */
        $order = $payment->getOrder();

        $providerConfig = $this->getProviderConfig($payment);

        if (empty($providerConfig)) {
            throw new \UnexpectedValueException('Payment parameter array should be provided');
        }

        $config = new Config();
        $this->setPaymentGatewayConfig($config, $providerConfig);

        PaymentGateway::setConfig($config);

        $request = new InitRequest();
        $this->setPaymentGatewayInitRequest($request, $order, $providerConfig);

        $response = PaymentGateway::init($request);

        $this->logger->debug((array)$response);

        if ($response->ResultCode === PaymentGateway::RESULT_CODE_SUCCESS) {
            $transaction = $this->helper->createTransaction();

            $transaction
                ->setOrderId($order->getOrderIncrementId())
                ->setTransactionId($response->TransactionId)
                ->setCreatedTime($this->dateTime->date())
                ->setStatus(Helper::TRANSACTION_STATUS_INITIALIZED)
                ->save();

            $this->helper->addTransactionLog($transaction, $response);
        } else {
            $message = $response->ResultCode . ': ' . $response->ResultMessage;
            $this->logger->critical($message);
            throw new \UnexpectedValueException($message);
        }
        return (array)$response;
    }

    /**
     * @param PaymentDataObjectInterface $payment
     * @return array
     */
    protected function getProviderConfig(PaymentDataObjectInterface $payment)
    {
        $methodCode = $payment->getPayment()->getMethodInstance()->getCode();

        return $this->providerConfig->getProviderConfig($methodCode);
    }

    /**
     * @param Config $config
     * @param array $providerConfig
     */
    protected function setPaymentGatewayConfig(Config $config, array $providerConfig)
    {
        $config->storeName = $providerConfig['storename'];
        $config->apiKey = $providerConfig['apikey'];
        $config->testMode = ((int)$providerConfig['testmode'] === 1);

        $this->logger->debug([
            'storeName' => $config->storeName,
            'apiKey' => $config->apiKey,
            'testMode' => $config->testMode,
            'moduleName' => $config->moduleName,
            'moduleVersion' => $config->moduleVersion,
        ]);
    }

    /**
     * @param InitRequest $request
     * @param OrderAdapterInterface $order
     * @param $providerConfig
     */
    protected function setPaymentGatewayInitRequest(
        InitRequest $request,
        OrderAdapterInterface $order,
        array $providerConfig
    ) {
        $request
            ->setProviderName($providerConfig['provider_code'])
            ->setResponseUrl($this->getStoreBaseUrl() . $providerConfig['responseUrl'])
            ->setAmount($order->getGrandTotalAmount())
            ->setCurrency($order->getCurrencyCode())
            ->setOrderId($order->getOrderIncrementId())
            ->setUserId($order->getCustomerId())
            ->setLanguage($this->getStoreLanguage())
            ->setModuleName('Magento (' . $this->productMetaData->getVersion() . ')')
            ->setModuleVersion($this->moduleList->getOne(Helper::MODULE_NAME)['setup_version'])
            ->setAutoCommit(true);

        if (isset($providerConfig['one_click_payment']) && (int)$providerConfig['one_click_payment'] === 1) {
            $request->setOneClickPayment(true);
        }

        $extraData = [];

        if ($providerConfig['name'] == ConfigProvider::CODE_KHB_SZEP) {
            $extraData['KhbCardPocketId'] = $providerConfig['card_pocket_id'];
        }

        if ($providerConfig['name'] == ConfigProvider::CODE_MKB_SZEP) {
            $request
                ->setMkbSzepCafeteriaId($providerConfig['card_pocket_id'])
                ->setGatewayPaymentPage(true);
        }

        if ($providerConfig['name'] == ConfigProvider::CODE_OTP_SZEP) {
            $request->setOtpCardPocketId($providerConfig['card_pocket_id']);
        }

        if ($providerConfig['name'] == ConfigProvider::CODE_SAFERPAY) {
            if (isset($providerConfig['payment_methods']) && strlen($providerConfig['payment_methods'])) {
                $extraData['SaferpayPaymentMethods'] = explode(',', $providerConfig['payment_methods']);
            }

            if (isset($providerConfig['wallets']) && strlen($providerConfig['wallets'])) {
                $extraData['SaferpayWallets'] = explode(',', $providerConfig['wallets']);
            }
        }

        if ($providerConfig['name'] == ConfigProvider::CODE_WIRECARD) {
            if (isset($providerConfig['payment_type']) && strlen($providerConfig['payment_type'])) {
                $extraData['QpayPaymentType'] = $providerConfig['payment_type'];
            }
        }

        if (!empty($extraData)) {
            $request->setExtra($extraData);
        }

        $this->logger->debug((array)$request);
    }

    /**
     * @return string
     */
    protected function getStoreBaseUrl()
    {
        return $this->storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_WEB);
    }

    /**
     * @return string
     */
    protected function getStoreLanguage()
    {
        return strtoupper(strstr($this->storeManager->getStore()->getLocaleCode(), '_', true));
    }

}