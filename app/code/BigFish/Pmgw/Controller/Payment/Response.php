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
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use BigFish\Pmgw\Gateway\Response\ResponseEvent;
use BigFish\PaymentGateway;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Model\Order;

/**
 * Class Response
 *
 * @package BigFish\Pmgw\Controller\Payment
 */
class Response extends Action
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \BigFish\Pmgw\Gateway\Response\ResponseEvent
     */
    protected $responseEvent;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Response constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \BigFish\Pmgw\Gateway\Response\ResponseEvent $responseEvent
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        ResponseEvent $responseEvent,
        CartManagementInterface $cartManagement,
        Quote $quote,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->responseEvent = $responseEvent;
        $this->cartManagement = $cartManagement;
        $this->quote = $quote;
        $this->resultRedirect = $context->getResultFactory();
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
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

        // TODO PaymentGateway::close szukseges lesz:
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