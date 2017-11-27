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
namespace BigFish\Pmgw\Controller\Payment;

use BigFish\Pmgw\Gateway\Helper\Helper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use BigFish\Pmgw\Gateway\Response\ResponseProcessor;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Method\Logger;
use BigFish\PaymentGateway;
use BigFish\PaymentGateway\Config;
use BigFish\PaymentGateway\Request\Result as ResultRequest;

class Response extends Action
{
    /**
     * @var ResponseProcessor
     */
    private $responseProcessor;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Response constructor.
     *
     * @param Context $context
     * @param ResponseProcessor $responseProcessor
     * @param ConfigInterface $config
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        ResponseProcessor $responseProcessor,
        ConfigInterface $config,
        Logger $logger
    ) {
        parent::__construct($context);

        $this->responseProcessor = $responseProcessor;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $urlParams = $this->getRequest()->getParams();

        if (!array_key_exists(Helper::RESPONSE_FIELD_TRANSACTION_ID, $urlParams)) {
            throw new \InvalidArgumentException(__('process_noTransactionIdInResponse'));
        }

        $config = new Config();
        $this->setPaymentGatewayConfig($config);

        PaymentGateway::setConfig($config);

        $response = PaymentGateway::result(
            new ResultRequest($urlParams[Helper::RESPONSE_FIELD_TRANSACTION_ID])
        );

        $this->logger->debug((array)$response);

        $this->responseProcessor->setResponse($response);

        $result = $this->responseProcessor->processResponse();

        switch ($result->getCode()) {
            case PaymentGateway::RESULT_CODE_TIMEOUT:
            case PaymentGateway::RESULT_CODE_ERROR:
            case PaymentGateway::RESULT_CODE_USER_CANCEL:
                $this->_redirect('checkout/onepage/failure', array('_secure'=>true));
                break;
            case PaymentGateway::RESULT_CODE_PENDING:
            case PaymentGateway::RESULT_CODE_SUCCESS:
                $this->_redirect('checkout/onepage/success', array('_secure'=>true));
                break;
        }
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
