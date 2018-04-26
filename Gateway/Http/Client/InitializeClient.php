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
namespace Bigfishpaymentgateway\Pmgw\Gateway\Http\Client;

use BigFish\PaymentGateway;
use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use BigFish\PaymentGateway\Config;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Customer\Model\Session;

class InitializeClient implements ClientInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

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
     * @param Helper $helper
     * @param Session $customerSession
     */
    public function __construct(
        ConfigInterface $config,
        Helper $helper,
        Session $customerSession
    ) {
        $this->config = $config;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();

        if ($request[Helper::RESPONSE_FIELD_RESULT_CODE] === PaymentGateway::RESULT_CODE_SUCCESS) {
            $this->helper->setPaymentGatewayConfig(
                $this->getPaymentGatewayConfig()
            );

            $url = $this->helper->getPaymentGatewayStartUrl($request[Helper::RESPONSE_FIELD_TRANSACTION_ID]);

            $this->customerSession->setPmgwRedirectUrlValue($url);

            $transaction = $this->helper->getTransactionByTransactionId($request[Helper::RESPONSE_FIELD_TRANSACTION_ID]);

            $this->helper->updateTransactionStatus($transaction, Helper::TRANSACTION_STATUS_STARTED);

            $this->helper->addTransactionLog($transaction, ['startUrl' => $url]);
        }
        return $request;
    }

    /**
     * @return Config
     */
    protected function getPaymentGatewayConfig()
    {
        $config = new Config();

        $config->storeName = $this->config->getValue('storename');
        $config->apiKey = $this->config->getValue('apikey');
        $config->testMode = ((int)$this->config->getValue('testmode') === 1);

        return $config;
    }

}
