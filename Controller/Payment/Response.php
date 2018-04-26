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
namespace Bigfishpaymentgateway\Pmgw\Controller\Payment;

use BigFish\PaymentGateway;
use BigFish\PaymentGateway\Config;
use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use Bigfishpaymentgateway\Pmgw\Gateway\Response\ResponseProcessor;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\ConfigInterface;

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
     * @var Helper
     */
    private $helper;

    /**
     * Response constructor.
     *
     * @param Context $context
     * @param ResponseProcessor $responseProcessor
     * @param ConfigInterface $config
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        ResponseProcessor $responseProcessor,
        ConfigInterface $config,
        Helper $helper
    ) {
        parent::__construct($context);

        $this->responseProcessor = $responseProcessor;
        $this->config = $config;
        $this->helper = $helper;
    }

    /**
     * @return mixed
     * @throws LocalizedException
     */
    public function execute()
    {
        $urlParams = $this->getRequest()->getParams();

        if (!array_key_exists(Helper::RESPONSE_FIELD_TRANSACTION_ID, $urlParams)) {
            throw new LocalizedException(__('Missing or invalid transaction id.'));
        }

        $this->helper->setPaymentGatewayConfig(
            $this->getPaymentGatewayConfig()
        );

        $response = $this->helper->getPaymentGatewayResult(
            $urlParams[Helper::RESPONSE_FIELD_TRANSACTION_ID]
        );

        $this->responseProcessor->setResponse($response);

        $result = $this->responseProcessor->processResponse();

        switch ($result->getCode()) {
            case PaymentGateway::RESULT_CODE_TIMEOUT:
            case PaymentGateway::RESULT_CODE_ERROR:
            case PaymentGateway::RESULT_CODE_USER_CANCEL:
                $this->_redirect('checkout/onepage/failure', ['_secure' => true]);
                break;
            case PaymentGateway::RESULT_CODE_PENDING:
            case PaymentGateway::RESULT_CODE_SUCCESS:
                $this->_redirect('checkout/onepage/success', ['_secure' => true]);
                break;
            default:
                throw new LocalizedException(__('Missing or invalid result code.'));
        }
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
