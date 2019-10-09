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
namespace Bigfishpaymentgateway\Pmgw\Gateway\Response;

use BigFish\PaymentGateway;
use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use Bigfishpaymentgateway\Pmgw\Model\ConfigProvider;
use Bigfishpaymentgateway\Pmgw\Model\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\OrderFactory;
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
     * @var Details
     */
    private $details;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var BuilderInterface
     */
    private $builder;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderFactory;

    /**
     * @param Order $order
     * @param OrderSender $orderSender
     * @param ResultInterface $result
     * @param LoggerInterface $logger
     * @param Helper $helper
     * @param BuilderInterface $builder
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Order $order,
        OrderSender $orderSender,
        ResultInterface $result,
        LoggerInterface $logger,
        Helper $helper,
        BuilderInterface $builder,
        OrderFactory $orderFactory
    ) {
        $this->order = $order;
        $this->orderSender = $orderSender;
        $this->result = $result;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->builder = $builder;
        $this->orderFactory = $orderFactory;
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
        } catch (\Exception $e) {
            $this->result->setCode(PaymentGateway::RESULT_CODE_ERROR);
            $this->result->setMessage($e->getMessage());
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

        $this->order = $this->orderFactory->create()->loadByIncrementId($this->transaction->getOrderId());

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
        $this->helper->createOrderTransaction($this->order, $this->response, PaymentTransaction::TYPE_ORDER);
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

        if ($this->isSuccessCardRegistration()) {
            $this->helper->setCardRegistration($this->transaction, true);
        }

        $this->helper->updateTransactionStatus($this->transaction, Helper::TRANSACTION_STATUS_SUCCESS);
        $this->helper->createOrderTransaction($this->order, $this->response, PaymentTransaction::TYPE_ORDER, $this->getProviderSpecificMessage());

        $this->logResponse();
    }

    /**
     * @return bool
     */
    protected function isSuccessCardRegistration()
    {
        $provider = $this->order->getPayment()->getMethod();

        if ($this->helper->isOneClickProvider($provider)) {
            $this->details = $this->helper->getPaymentGatewayDetails($this->response->TransactionId);

            if (isset($this->details->ProviderSpecificData->OneClickPayment) && !empty($this->details->ProviderSpecificData->OneClickPayment)) {
                if (
                    $provider == ConfigProvider::CODE_BORGUN2 ||
                    $provider == ConfigProvider::CODE_VIRPAY
                ) {
                    if (!isset($this->details->ProviderSpecificData->ParentBorgunTransactionId) || empty($this->details->ProviderSpecificData->ParentBorgunTransactionId)) {
                        return true;
                    }
                }

                if ($provider == ConfigProvider::CODE_BARION2) {
                    if (!isset($this->details->ProviderSpecificData->RecurrenceId) || empty($this->details->ProviderSpecificData->RecurrenceId)) {
                        return true;
                    }
                }

                if ($provider == ConfigProvider::CODE_GP) {
                    if (!isset($this->details->ProviderSpecificData->ParentOrdernumber) || empty($this->details->ProviderSpecificData->ParentOrdernumber)) {
                        return true;
                    }
                }

                if ($provider == ConfigProvider::CODE_SAFERPAY) {
                    if (!isset($this->details->ProviderSpecificData->ParentSaferpayTransactionId) || empty($this->details->ProviderSpecificData->ParentSaferpayTransactionId)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return null|string
     */
    protected function getProviderSpecificMessage()
    {
        $provider = $this->order->getPayment()->getMethod();

        if (!in_array($provider, array(ConfigProvider::CODE_OTPARUHITEL))) {
            return;
        }

        $details = $this->helper->getPaymentGatewayDetails($this->response->TransactionId);

        if ($provider == ConfigProvider::CODE_OTPARUHITEL) {
            return sprintf(
                '%s, %s',
                __('Credit amount: %1 %2', $details->ProviderSpecificData->CreditAmount, $details->ProviderSpecificData->Currency),
                __('Contribution: %1 %2', $details->ProviderSpecificData->Contribution, $details->ProviderSpecificData->Currency)
            );
        }
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
        $this->helper->createOrderTransaction($this->order, $this->response, PaymentTransaction::TYPE_ORDER);
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
