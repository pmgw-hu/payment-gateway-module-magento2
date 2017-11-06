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

/**
 * Class Response
 *
 * @package BigFish\Pmgw\Controller\Payment
 */
class Response extends Action
{
    /**
     * @var \BigFish\Pmgw\Gateway\Response\ResponseEvent
     */
    protected $responseEvent;

    /**
     * Response constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \BigFish\Pmgw\Gateway\Response\ResponseEvent $responseEvent
     */
    public function __construct(
        Context $context,
        ResponseEvent $responseEvent
    ) {
        parent::__construct($context);
        $this->responseEvent = $responseEvent;
    }

    /**
     * @return mixed
     */
    public function execute() {

        $urlParams = $this->getRequest()->getParams();
        if(!array_key_exists(Helper::TXN_ID, $urlParams))
        {
            throw new \InvalidArgumentException(__('process_noTransactionIdInResponse'));
        }

        $transactionId = $urlParams[Helper::TXN_ID];

        $response = PaymentGateway::result(new PaymentGateway\Request\Result($transactionId));

        $responseArray = [];
        if (is_object($response)) {
            foreach (get_object_vars($response) as $response_key => $response_val) {
                $responseArray[$response_key] = $response_val;
            }
        } else {
            $responseArray = $response;
        }

        $this->responseEvent->setEventData($responseArray);

        $status = $this->responseEvent->processStatusEvent();

        $response = PaymentGateway::close(new PaymentGateway\Request\Close($transactionId, true));

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

        return $status['message'];
    }

}