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
namespace BigFish\Pmgw\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use BigFish\PaymentGateway;
use BigFish\Pmgw\Gateway\Helper\Helper;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\ConfigInterface;
use BigFish\PaymentGateway\Config;
use Magento\Customer\Model\Session;

class InitializeClient implements ClientInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param ConfigInterface $config
     * @param Logger $logger
     * @param Helper $helper
     * @param Session $customerSession
     */
    public function __construct(
        ConfigInterface $config,
        Logger $logger,
        Helper $helper,
        Session $customerSession
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $response = $transferObject->getBody();

        if ($response[Helper::RESPONSE_FIELD_RESULT_CODE] === PaymentGateway::RESULT_CODE_SUCCESS) {
            $config = new Config();
            $this->setPaymentGatewayConfig($config);

            $url = PaymentGateway::getStartUrl(new PaymentGateway\Request\Start($response[Helper::RESPONSE_FIELD_TRANSACTION_ID]));

            $this->customerSession->setPmgwRedirectUrlValue($url);

            $transaction = $this->helper->getTransactionByTransactionId($response[Helper::RESPONSE_FIELD_TRANSACTION_ID]);

            $this->helper->updateTransactionStatus($transaction, Helper::TRANSACTION_STATUS_STARTED);

            $this->helper->addTransactionLog($transaction, ['startUrl' => $url]);

            $this->logger->debug(['startUrl' => $url]);
        }
        return $response;
    }

    /**
     * @param Config $config
     */
    protected function setPaymentGatewayConfig(Config $config)
    {
        $config->storeName = $this->config->getValue('storename');
        $config->apiKey = $this->config->getValue('apikey');
        $config->testMode = ((int)$this->config->getValue('testmode') === 1);

        $this->logger->debug([
            'storeName' => $config->storeName,
            'apiKey' => $config->apiKey,
            'testMode' => $config->testMode,
            'moduleName' => $config->moduleName,
            'moduleVersion' => $config->moduleVersion,
        ]);
    }

}
