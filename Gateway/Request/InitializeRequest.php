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
namespace Bigfishpaymentgateway\Pmgw\Gateway\Request;

use Bigfishpaymentgateway\Pmgw\Model\ConfigProvider;
use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use BigFish\PaymentGateway;
use BigFish\PaymentGateway\Config;
use BigFish\PaymentGateway\Request\Init as InitRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Backend\Model\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class InitializeRequest implements BuilderInterface
{
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param ConfigProvider $providerConfig
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param ModuleListInterface $moduleList
     * @param Helper $helper
     * @param LoggerInterface $logger
     * @param DateTime $dateTime
     */
    public function __construct(
        ConfigProvider $providerConfig,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata,
        ModuleListInterface $moduleList,
        Helper $helper,
        LoggerInterface $logger,
        DateTime $dateTime
    ) {
        $this->providerConfig = $providerConfig;
        $this->storeManager = $storeManager;
        $this->productMetaData = $productMetadata;
        $this->moduleList = $moduleList;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
    }

    /**
     * @param array $buildSubject
     * @return array
     * @throws \Exception
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

        $this->helper->setPaymentGatewayConfig(
            $this->getPaymentGatewayConfig($providerConfig)
        );

        $response = $this->helper->initializePaymentGatewayTransaction(
            $this->getPaymentGatewayInitRequest($order, $providerConfig)
        );

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
     * @throws LocalizedException
     */
    protected function getProviderConfig(PaymentDataObjectInterface $payment)
    {
        $methodCode = $payment->getPayment()->getMethodInstance()->getCode();

        return $this->providerConfig->getProviderConfig($methodCode);
    }

    /**
     * @param array $providerConfig
     * @return Config
     */
    protected function getPaymentGatewayConfig(array $providerConfig)
    {
        $config = new Config();

        $config->storeName = $providerConfig['storename'];
        $config->apiKey = $providerConfig['apikey'];
        $config->testMode = ((int)$providerConfig['testmode'] === 1);

        return $config;
    }

    /**
     * @param OrderAdapterInterface $order
     * @param $providerConfig
     * @return InitRequest
     * @throws \Exception
     */
    protected function getPaymentGatewayInitRequest(OrderAdapterInterface $order, array $providerConfig)
    {
        $request = new InitRequest();

        $request
            ->setProviderName($providerConfig['provider_code'])
            ->setResponseUrl($this->getStoreBaseUrl() . $providerConfig['response_url'])
            ->setAmount($order->getGrandTotalAmount())
            ->setCurrency($order->getCurrencyCode())
            ->setOrderId($order->getOrderIncrementId())
            ->setUserId($order->getCustomerId())
            ->setLanguage($this->getStoreLanguage())
            ->setModuleName('Magento (' . $this->productMetaData->getVersion() . ')')
            ->setModuleVersion($this->moduleList->getOne(Helper::MODULE_NAME)['setup_version'])
            ->setAutoCommit(true);

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

        return $request;
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
