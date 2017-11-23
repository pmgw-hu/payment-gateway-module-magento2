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
use BigFish\Pmgw\Gateway\Response\ResponseEvent;
use BigFish\PaymentGateway;
use Magento\Payment\Model\Method\Logger;

class Response extends Action
{
    /**
     * @var ResponseEvent
     */
    private $responseEvent;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Response constructor.
     *
     * @param Context $context
     * @param ResponseEvent $responseEvent
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        ResponseEvent $responseEvent,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->responseEvent = $responseEvent;
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

        $response = PaymentGateway::result(
            new PaymentGateway\Request\Result($urlParams[Helper::RESPONSE_FIELD_TRANSACTION_ID])
        );

        $this->logger->debug((array)$response);

        $this->responseEvent->setEventData((array)$response);

        $status = $this->responseEvent->processStatusEvent();

        switch ($status['resultCode']) {
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

}
