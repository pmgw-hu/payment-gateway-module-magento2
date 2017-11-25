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
namespace BigFish\Pmgw\Gateway\Helper;

use Magento\Braintree\Model\Paypal\Helper\AbstractHelper;
use BigFish\Pmgw\Model\TransactionFactory;
use BigFish\Pmgw\Model\LogFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use BigFish\Pmgw\Model\Transaction;

class Helper extends AbstractHelper
{
    const MODULE_NAME = 'BigFish_Pmgw';

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
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        TransactionFactory $transactionFactory,
        LogFactory $logFactory,
        JsonHelper $jsonHelper,
        DateTime $dateTime
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->logFactory = $logFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dateTime = $dateTime;
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

}
