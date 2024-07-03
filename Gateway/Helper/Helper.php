<?php
/**
 * BIG FISH Payment Services Ltd.
 * https://paymentgateway.hu
 *
 * @title      BIG FISH Payment Gateway module for Magento 2
 * @category   BigFish
 * @package    Bigfishpaymentgateway_Pmgw
 * @author     BIG FISH Payment Services Ltd., it [at] paymentgateway [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2024, BIG FISH Payment Services Ltd.
 */

namespace Bigfishpaymentgateway\Pmgw\Gateway\Helper;

use BigFish\PaymentGateway;
use BigFish\PaymentGateway\Config;
use BigFish\PaymentGateway\Request\Start as StartRequest;
use BigFish\PaymentGateway\Request\Init as InitRequest;
use BigFish\PaymentGateway\Request\Result as ResultRequest;
use BigFish\PaymentGateway\Request\Details as DetailsRequest;
use BigFish\PaymentGateway\Request\GetPaymentRegistrations as GetPaymentRegistrationsRequest;
use BigFish\PaymentGateway\Transport\Response\Response;
use Bigfishpaymentgateway\Pmgw\Model\TransactionFactory;
use Bigfishpaymentgateway\Pmgw\Model\Transaction;
use Bigfishpaymentgateway\Pmgw\Model\LogFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface as TransactionBuilderInterface;
use Bigfishpaymentgateway\Pmgw\Model\ConfigProvider;

class Helper extends AbstractHelper
{
    const MODULE_NAME = 'Bigfishpaymentgateway_Pmgw';

    const RESPONSE_FIELD_TRANSACTION_ID = 'TransactionId';
    const RESPONSE_FIELD_RESULT_CODE = 'ResultCode';
    const RESPONSE_FIELD_RESULT_MESSAGE = 'ResultMessage';

    const TRANSACTION_STATUS_INITIALIZED = 100;
    const TRANSACTION_STATUS_STARTED = 110;
    const TRANSACTION_STATUS_PENDING = 120;
    const TRANSACTION_STATUS_SUCCESS = 200;
    const TRANSACTION_STATUS_FAILED = 210;
    const TRANSACTION_STATUS_CANCELLED = 220;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @var Logger
     */
    private $paymentLogger;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TransactionBuilderInterface
     */
    private $transactionBuilder;

    /**
     * @var PaymentGateway
     */
    private $paymentGateway;

    public function __construct(
        TransactionFactory $transactionFactory,
        TransactionBuilderInterface $transactionBuilder,
        LogFactory $logFactory,
        Logger $paymentLogger,
        JsonHelper $jsonHelper,
        DateTime $dateTime
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->logFactory = $logFactory;
        $this->paymentLogger = $paymentLogger;
        $this->jsonHelper = $jsonHelper;
        $this->dateTime = $dateTime;
        $this->transactionBuilder = $transactionBuilder;
    }

    /**
     * @return Transaction
     */
    public function createTransaction()
    {
        return $this->transactionFactory->create();
    }

    /**
     * @param string $transactionId
     * @return Transaction
     */
    public function getTransactionByTransactionId($transactionId)
    {
        return $this->transactionFactory->create()->load($transactionId, 'transaction_id');
    }

    /**
     * @param Transaction $transaction
     * @param int $status
     */
    public function updateTransactionStatus(Transaction $transaction, $status)
    {
        $transaction->setStatus($status)->save();
    }

    /**
     * @param Transaction $transaction
     * @param boolean $data
     */
    public function setCardRegistration(Transaction $transaction, $data = false)
    {
        $transaction->setCardRegistration($data)->save();
    }

    /**
     * @param Transaction $transaction
     * @param array|object $debug
     */
    public function addTransactionLog(Transaction $transaction, $debug)
    {
        $this->logFactory->create()
            ->setPaymentgatewayId($transaction->getId())
            ->setStatus($transaction->getStatus())
            ->setCreatedTime($this->dateTime->date())
            ->setDebug($this->jsonHelper->jsonEncode($debug))
            ->save();
    }

    /**
     * @param Transaction $transaction
     * @return DataObject
     */
    public function getTransactionLog(Transaction $transaction)
    {
        return $this->logFactory->create()->getCollection()
            ->addFilter('paymentgateway_id', $transaction->getId())
            ->addFilter('status', $transaction->getStatus())
            ->getFirstItem();
    }

    /**
     * @param Config $config
     */
    public function setPaymentGatewayConfig(Config $config)
    {
        $this->paymentGateway = new PaymentGateway($config);

        $this->debug([
            'action' => 'setConfig',
            'data' => [
                'storeName' => $config->getStoreName(),
                'apiKey' => $config->getApiKey(),
                'testMode' => $config->isTestMode(),
            ]
        ]);
    }

    /**
     * @param string $transactionId
     * @return string
     */
    public function getPaymentGatewayStartUrl($transactionId)
    {
        $startUrl = $this->paymentGateway->getRedirectUrl((new StartRequest())->setTransactionId($transactionId));

        $this->debug([
            'action' => 'getStartUrl',
            [
                'transactionId' => $transactionId,
                'startUrl' => $startUrl,
            ]
        ]);

        return $startUrl;
    }

    /**
     * @param InitRequest $request
     * @return Response
     */
    public function initializePaymentGatewayTransaction(InitRequest $request)
    {
        $response = $this->paymentGateway->send($request);

        $this->debug([
            'action' => 'init',
            'request' => $request->getData(),
            'response' => $response->getData(),
        ]);

        return $response;
    }

    /**
     * @param string $transactionId
     * @return Response
     */
    public function getPaymentGatewayResult($transactionId)
    {
        $response = $this->paymentGateway->send((new ResultRequest())->setTransactionId($transactionId));

        $this->debug([
            'action' => 'result',
            'transactionId' => $transactionId,
            'response' => $response->getData(),
        ]);

        return $response;
    }

    /**
     * @param string $transactionId
     * @return Response
     */
    public function getPaymentGatewayDetails($transactionId)
    {
        $response = $this->paymentGateway->send(
            (new DetailsRequest())
                ->setTransactionId($transactionId)
                ->setGetRelatedTransactions(false)
                ->setGetInfoData(false)
        );

        $this->debug([
            'action' => 'details',
            'transactionId' => $transactionId,
            'response' => $response->getData(),
        ]);

        return $response;
    }

    /**
     * @param GetPaymentRegistrationsRequest $getPaymentRegistrationsRequest
     * @return Response
     * @throws PaymentGateway\Exception
     */
    public function getPaymentRegistrations(GetPaymentRegistrationsRequest $getPaymentRegistrationsRequest)
    {
        $response = $this->paymentGateway->send($getPaymentRegistrationsRequest);

        $this->debug([
            'action' => 'getPaymentRegistrations',
            'request' => $getPaymentRegistrationsRequest->getData(),
            'response' => $response->getData(),
        ]);

        return $response;
    }

    /**
     * @param array $data
     */
    protected function debug(array $data)
    {
        $this->paymentLogger->debug($data);
    }

    /**
     * @param string $configProvider
     * @return bool
     */
    public function isOneClickProvider($configProvider)
    {
        switch ($configProvider) {
            case ConfigProvider::CODE_SAFERPAY:
            case ConfigProvider::CODE_BARION2:
            case ConfigProvider::CODE_BORGUN2:
            case ConfigProvider::CODE_GP:
            case ConfigProvider::CODE_VIRPAY:
            case ConfigProvider::CODE_PAYPALREST:
            case ConfigProvider::CODE_PAYUREST:
            case ConfigProvider::CODE_KHB:
                return true;
            default:
                return false;
        }
    }

    /**
     * @param Order $order
     * @param Response $response
     * @param string $transactionType
     * @param null|string $additionalComment
     */
    public function createOrderTransaction(Order $order, Response $response, $transactionType, $additionalComment = null)
    {
        $payment = $order->getPayment();
        $payment->setLastTransId($response->TransactionId);
        $payment->setTransactionId($response->TransactionId);
        $payment->setAdditionalInformation(
            [PaymentTransaction::RAW_DETAILS => $response->getData()]
        );

        $trans = $this->transactionBuilder;
        $transaction = $trans->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($response->TransactionId)
            ->setAdditionalInformation(
                [PaymentTransaction::RAW_DETAILS => $response->getData()]
            )
            ->setFailSafe(true)
            ->build($transactionType);

        $payment->addTransactionCommentsToOrder(
            $transaction,
            __('Result code: %1.', $response->ResultCode)
        );

        if (!empty($additionalComment)) {
            $payment->addTransactionCommentsToOrder(
                $transaction,
                $additionalComment
            );
        }

        $payment->setParentTransactionId(null);
        $payment->save();
        $order->save();
        $transaction->save()->getTransactionId();
    }
}
