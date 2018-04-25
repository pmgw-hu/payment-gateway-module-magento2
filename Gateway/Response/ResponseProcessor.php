<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
 * @category   BigFish
 * @package    Bigfishpaymentgateway_Pmgw
 * @author     BIG FISH Ltd., paymentgateway [at] bigfish [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */
namespace Bigfishpaymentgateway\Pmgw\Gateway\Response;

use BigFish\PaymentGateway;
use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use Bigfishpaymentgateway\Pmgw\Model\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use BigFish\PaymentGateway\Response;
use Bigfishpaymentgateway\Pmgw\Model\Response\ResultInterface;

class ResponseProcessor
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var ResultInterface
     */
    private $result;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @param Order $order
     * @param OrderSender $orderSender
     * @param ResultInterface $result
     * @param LoggerInterface $logger
     * @param Helper $helper
     */
    public function __construct(
        Order $order,
        OrderSender $orderSender,
        ResultInterface $result,
        LoggerInterface $logger,
        Helper $helper
    ) {
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->result = $result;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return ResultInterface
     */
    public function processResponse()
    {
        try {
            $this->validateResponse();

            switch ($this->response->ResultCode) {
                case PaymentGateway::RESULT_CODE_PENDING:
                    $this->processPending();
                    break;
                case PaymentGateway::RESULT_CODE_SUCCESS:
                    $this->processSuccess();
                    break;
                case PaymentGateway::RESULT_CODE_USER_CANCEL:
                    $this->processFailure(Helper::TRANSACTION_STATUS_CANCELLED);
                    break;
                case PaymentGateway::RESULT_CODE_ERROR:
                case PaymentGateway::RESULT_CODE_TIMEOUT:
                    $this->processFailure(Helper::TRANSACTION_STATUS_FAILED);
                    break;
            }

            $this->result->setCode($this->response->ResultCode);
            $this->result->setMessage($this->response->ResultMessage);
        } catch (LocalizedException $e) {
            $this->result->setCode(PaymentGateway::RESULT_CODE_ERROR);
            $this->result->setMessage($e->getMessage());
            $this->logger->critical($e);
        } catch (\Exception $e) {
            $this->result->setCode(PaymentGateway::RESULT_CODE_ERROR);
            $this->logger->critical($e);
        }
        return $this->result;
    }

    protected function validateResponse()
    {
        if (!property_exists($this->response, 'TransactionId') || empty($this->response->TransactionId)) {
            throw new LocalizedException(__('Missing or invalid transaction id.'));
        }

        if (!property_exists($this->response, 'ResultCode') || empty($this->response->ResultCode)) {
            throw new LocalizedException(__('Missing or invalid result code.'));
        }

        $this->transaction = $this->helper->getTransactionByTransactionId($this->response->TransactionId);

        if (!$this->transaction || !$this->transaction->getId()) {
            throw new LocalizedException(__('Transaction not found.'));
        }

        $this->order->loadByIncrementId($this->transaction->getOrderId());

        if (!$this->order->getId()) {
            throw new LocalizedException(__('Order not found.'));
        }

        if (strpos($this->order->getPayment()->getMethod(), 'bigfishpaymentgateway_pmgw_') !== 0) {
            throw new LocalizedException(__('Invalid payment method.'));
        }
    }

    protected function processPending()
    {
        $this->order->setState(Order::STATE_PENDING_PAYMENT);
        $this->order->getPayment()->setLastTransId($this->response->TransactionId);
        $this->order->save();

        $this->helper->updateTransactionStatus($this->transaction, Helper::TRANSACTION_STATUS_PENDING);
        $this->logResponse();
    }

    protected function processSuccess()
    {
        $this->createInvoice();

        $this->order->setState(Order::STATE_PROCESSING);
        $this->order->getPayment()->setLastTransId($this->response->TransactionId);

        if (property_exists($this->response, 'Anum')) {
            $this->order->getPayment()->setPoNumber($this->response->Anum);
        }
        $this->orderSender->send($this->order, false);
        $this->order->save();

        $this->helper->updateTransactionStatus($this->transaction, Helper::TRANSACTION_STATUS_SUCCESS);
        $this->logResponse();
    }

    /**
     * @param int $transactionStatus
     */
    protected function processFailure($transactionStatus)
    {
        $this->order->getPayment()->setLastTransId($this->response->TransactionId);
        $this->order->cancel();
        $this->order->save();

        $this->helper->updateTransactionStatus($this->transaction, $transactionStatus);
        $this->logResponse();
    }

    protected function createInvoice()
    {
        if (!$this->order->canInvoice()) {
            return;
        }
        $invoice = $this->order->prepareInvoice();
        $invoice->register()->capture();
        $this->order->addRelatedObject($invoice);
    }

    protected function logResponse()
    {
        $this->helper->addTransactionLog($this->transaction, $this->response);
    }

}
