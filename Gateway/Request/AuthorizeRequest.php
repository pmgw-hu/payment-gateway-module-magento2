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
use BigFish\Pmgw\Model\TransactionFactory;
use BigFish\Pmgw\Model\LogFactory;
use BigFish\Pmgw\Gateway\Helper\Helper;
use BigFish\PaymentGateway;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use BigFish\PaymentGateway\Config;
use BigFish\PaymentGateway\Request\Init as InitRequest;
use BigFish\PaymentGateway\Response;

class AuthorizeRequest implements BuilderInterface
{
    const MODULE_NAME = 'BigFish_Pmgw';

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
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @param ConfigProvider $providerConfig
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $productMetadata
     * @param ModuleListInterface $moduleList
     * @param TransactionFactory $transactionFactory
     * @param LogFactory $logFactory
     */
    public function __construct(
        ConfigProvider $providerConfig,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $productMetadata,
        ModuleListInterface $moduleList,
        TransactionFactory $transactionFactory,
        LogFactory $logFactory
    ) {
        $this->providerConfig = $providerConfig;
        $this->storeManager = $storeManager;
        $this->productMetaData = $productMetadata;
        $this->moduleList = $moduleList;
        $this->transactionFactory = $transactionFactory;
        $this->logFactory = $logFactory;
    }

    /**
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

        if ($response->ResultCode === PaymentGateway::RESULT_CODE_SUCCESS) {
            $this->saveTransaction($order, $response);
        } else {
            throw new \UnexpectedValueException(sprintf(
                '%s: %s',
                $response->ResultCode,
                $response->ResultMessage
            ));
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
            ->setModuleVersion($this->moduleList->getOne(self::MODULE_NAME)['setup_version'])
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
    }

    /**
     * @param OrderAdapterInterface $order
     * @param Response $response
     */
    protected function saveTransaction(OrderAdapterInterface $order, Response $response)
    {
        $transactionFactory = $this->transactionFactory->create();

        $transactionFactory->setOrderId($order->getOrderIncrementId())
            ->setTransactionId($response->TransactionId)
            ->setCreatedTime(date("Y-m-d H:i:s"))
            ->setStatus(Helper::TRANSACTION_STATUS_INITIALIZED)
            ->save();

        $transactionId = $transactionFactory->getId();

        $this->saveTransactionLog($order, $response, $transactionId);
    }

    /**
     * @param OrderAdapterInterface $order
     * @param Response $response
     * @param int $transactionId
     */
    protected function saveTransactionLog(OrderAdapterInterface $order, Response $response, $transactionId)
    {
        $this->logFactory->create()->setPaymentgatewayId($transactionId)
            ->setOrderId($order->getOrderIncrementId())
            ->setTransactionId($response->TransactionId)
            ->setCreatedTime(date("Y-m-d H:i:s"))
            ->setStatus(Helper::TRANSACTION_STATUS_INITIALIZED)
            ->setDebug(print_r($response, true))
            ->save();
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
