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

use BigFish\Pmgw\Model\LogFactory;
use BigFish\Pmgw\Model\TransactionFactory;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Framework\App\ObjectManager;
use BigFish\PaymentGateway;
use BigFish\Pmgw\Gateway\Helper\Helper;
use Magento\Payment\Model\Method\Logger;
use BigFish\Pmgw\Model\Transaction;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class InitializeClient implements ClientInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger,
        TransactionFactory $transactionFactory,
        LogFactory $logFactory,
        JsonHelper $jsonHelper
    ) {
        $this->logger = $logger;
        $this->transactionFactory = $transactionFactory;
        $this->logFactory = $logFactory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $response = $transferObject->getBody();

        if ($response[Helper::RESPONSE_FIELD_RESULT_CODE] === PaymentGateway::RESULT_CODE_SUCCESS) {
            $url = PaymentGateway::getStartUrl(new PaymentGateway\Request\Start($response[Helper::RESPONSE_FIELD_TRANSACTION_ID]));

            ObjectManager::getInstance()->create('Magento\Customer\Model\Session')
                ->setPmgwRedirectUrlValue($url);

            $transaction = $this->getTransactionByTransactionId($response[Helper::RESPONSE_FIELD_TRANSACTION_ID]);

            $this->updateTransactionStatus($transaction, Helper::TRANSACTION_STATUS_STARTED);

            $this->addTransactionLog($transaction, $this->jsonHelper->jsonEncode(['startUrl' => $url]));

            $this->logger->debug(['startUrl' => $url,]);
        }
        return $response;
    }

    /**
     * @param Transaction $transaction
     * @param int $status
     */
    protected function updateTransactionStatus(Transaction $transaction, $status)
    {
        $transaction->setStatus($status)->save();
    }

    /**
     * @param Transaction $transaction
     * @param string $debug
     */
    protected function addTransactionLog(Transaction $transaction, $debug)
    {
        $this->logFactory->create()
            ->setPaymentgatewayId($transaction->getId())
            ->setStatus($transaction->getStatus())
            ->setCreatedTime(date("Y-m-d H:i:s"))
            ->setDebug($debug)
            ->save();
    }

    /**
     * @param string $transactionId
     * @return Transaction|null
     */
    protected function getTransactionByTransactionId($transactionId)
    {
        return $this->transactionFactory->create()->load($transactionId, 'transaction_id');
    }

}
