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
namespace BigFish\Pmgw\Gateway\Response;

use BigFish\PaymentGateway;
use BigFish\Pmgw\Gateway\Helper\Helper;
use Magento\Sales\Model\Order;
use Magento\Checkout\Model\Session;
use Psr\Log\LoggerInterface;
use BigFish\Pmgw\Model\Resource\Paymentgateway\Collection;
use BigFish\Pmgw\Model\Resource\Log\LogCollection;
use Magento\Sales\Api\Data\OrderInterface;
use BigFish\Pmgw\Model\PmgwAbstractFactory;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * Class ResponseEvent
 *
 * @package BigFish\Pmgw\Gateway\Response
 */
class ResponseEvent
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order = null;

    /**
     * Event request data
     * @var array
     */
    protected $_eventData = array();

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Collection
     */
    protected $paymentGatewayCollection;

    /**
     * @var \BigFish\Pmgw\Model\PmgwAbstractFactory
     */
    protected $pmgwFactory;

    /**
     * @var \BigFish\Pmgw\Model\Resource\Log\LogCollection
     */
    protected $paymentGatewayLogCollection;


    /**
     * ResponseEvent constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param \BigFish\Pmgw\Model\Resource\Paymentgateway\Collection $paymentGatewayCollection
     * @param \Magento\Sales\Api\Data\OrderInterface $salesOrderFactory
     * @param \BigFish\Pmgw\Model\Resource\Log\LogCollection $paymentGatewayLogCollection
     * @param \BigFish\Pmgw\Model\PmgwAbstractFactory $pmgwFactory
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     */
    public function __construct(
        Session $checkoutSession,
        LoggerInterface $logger,
        Collection $paymentGatewayCollection,
        OrderInterface $salesOrderFactory,
        LogCollection $paymentGatewayLogCollection,
        PmgwAbstractFactory $pmgwFactory,
        OrderSender $orderSender
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->paymentGatewayCollection = $paymentGatewayCollection;
        $this->order = $salesOrderFactory;
        $this->paymentGatewayLogCollection = $paymentGatewayLogCollection;
        $this->pmgwFactory = $pmgwFactory;
        $this->orderSender = $orderSender;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function setOrder(Order $order) {
        $this->order = $order;
    }

    /**
     * Event request data setter
     *
     * @param array $data
     *
     * @return $this
     */
    public function setEventData(array $data)
    {
        $this->_eventData = $data;
        return $this;
    }

    /**
     * Event request data getter
     *
     * @param string $key
     * @return array|string
     */
    public function getEventData($key = null)
    {
        if (null === $key) {
            return $this->_eventData;
        }
        return isset($this->_eventData[$key]) ? $this->_eventData[$key] : null;
    }

    /**
     * Get singleton of Checkout Session Model
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->checkoutSession;
    }

    /**
     * Process status notification from PaymentGateway
     *
     * @return String
     */
    public function processStatusEvent()
    {
        try {
			$response = array();
			
			if (is_array($this->_eventData) && count($this->_eventData)) {
				$response[] = $this->_eventData['ResultMessage'].'<br />';
				
				if (strlen($this->_eventData['ProviderTransactionId'])) {
					$response[] = __('Provider Transaction ID').': '.$this->_eventData['ProviderTransactionId'];
				}
				
				if (strlen($this->_eventData['Anum'])) {
					$response[] = __('Anum').': '.$this->_eventData['Anum'];
				}
			}

            $params = $this->_validateEventData(false);
            $msg = '';

            switch($params['ResultCode']) {
                case PaymentGateway::RESULT_CODE_TIMEOUT:
					$msg = (count($response) ? implode('<br />', $response) : __('status_paymentTimeout'));
                    $this->_processCancel($msg);
                    break;
                case PaymentGateway::RESULT_CODE_ERROR:
                    $msg = (count($response) ? implode('<br />', $response) : __('status_paymentFailed'));
                    $this->_processCancel($msg);
                    break;
                case PaymentGateway::RESULT_CODE_USER_CANCEL:
					$msg = (count($response) ? implode('<br />', $response) : __('status_paymentCancelled'));
                    $this->_processCancel($msg);
                    break;
                case PaymentGateway::RESULT_CODE_PENDING:
                    $msg = __('status_paymentPending');
                    $this->_processSale($params['ResultCode'], $msg);
                    break;
                case PaymentGateway::RESULT_CODE_SUCCESS:
					$msg = (count($response) ? implode('<br />', $response) : __('status_paymentSuccess'));
                    $this->_processSale($params['ResultCode'], $msg);
                    break;
            }

            return $msg;

        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            $this->logger->critical($e);

        }
    }

    /**
     * Process cancelation
     */
    public function cancelEvent() {
        try {
            $this->_validateEventData(false);
            $this->_processCancel(__('status_paymentCancelled'));
            return __('event_orderCancelled');
        } catch (Mage_Core_Exception $e) {
            return $e->getMessage();
        } catch(Exception $e) {
            $this->logger->critical($e);
        }
        return '';
    }

    /**
     * Validate request and return QuoteId
     * Can throw Mage_Core_Exception and Exception
     *
     * @return int
     */
    public function successEvent(){
        $this->_validateEventData(false);
        return $this->_order->getQuoteId();
    }

    /**
     * Processed order cancelation
     * @param string $msg Order history message
     */
    protected function _processCancel($msg)
    {
        $this->_setTransactionStatus(Helper::TRANSACTION_STATUS_CANCELLED);
        $this->_addTransactionLog($msg."\nRESPONSE:\n".print_r($this->_eventData, true));
        $this->order->cancel();
        $this->order->addStatusToHistory(Order::STATE_CANCELED, $msg);
        $this->order->save();
    }

    /**
     * Processes payment confirmation, creates invoice if necessary, updates order status,
     * sends order confirmation to customer
     *
     * @param $status
     * @param string $msg Order history message
     */
    protected function _processSale($status, $msg)
    {
        switch ($status) {
            case PaymentGateway::RESULT_CODE_SUCCESS:

                $this->_setTransactionStatus(Helper::TRANSACTION_STATUS_SUCCESS);
                //$this->_addTransactionLog($msg."\nRESPONSE:\n".print_r($this->_eventData, true));

                $this->_createInvoice();
                $this->order->setState(Order::STATE_PROCESSING, true, $msg);
                // save transaction ID
                $this->order->getPayment()->setLastTransId($this->getEventData('TransactionId'));
                $this->order->getPayment()->setPoNumber($this->getEventData('Anum'));

                // send new order email
                $this->orderSender->send($this->order, false, true);
                break;
            case PaymentGateway::RESULT_CODE_PENDING:

                $this->_addTransactionLog($msg."\nRESPONSE:\n".print_r($this->_eventData, true));

                $this->order->setState(Order::STATE_PENDING_PAYMENT, true, $msg);
                // save transaction ID
                $this->order->getPayment()->setLastTransId($this->getEventData('ProviderTransactionId'));
                $this->order->getPayment()->setPoNumber($this->getEventData('Anum'));
                break;
        }
        $this->order->save();
    }

    /**
     * Builds invoice for order
     */
    protected function _createInvoice()
    {
        if (!$this->order->canInvoice()) {
            return;
        }
        $invoice = $this->order->prepareInvoice();
        $invoice->register()->capture();
        $this->order->addRelatedObject($invoice);
    }

    /**
     * Checking returned parameters
     * Thorws Mage_Core_Exception if error
     *
     * @param bool $fullCheck Whether to make additional validations such as payment status
     *
     * @return array $params request params
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _validateEventData($fullCheck = true)
    {
        // get request variables
        $params = $this->_eventData;
        if (empty($params)) {
            throw new \Magento\Framework\Exception\LocalizedException('Request does not contain any elements.');
        }

        // check Transaction ID
        if (empty($params[Helper::TXN_ID])) {
            throw new \Magento\Framework\Exception\LocalizedException('Missing or invalid order ID.');
        }

        $pmgwCollection = $this->pmgwFactory->create()->getCollection();
        $pmgwCollection->addFieldToSelect('*')
                    ->addFieldToFilter('transaction_id',array('eq'=>$params[Helper::TXN_ID]))
                    ->addOrder('created_time','desc')
                    ->load();

        if($pmgwCollection->getSize()==0) {
            throw new \Magento\Framework\Exception\LocalizedException('Invalid Transaction Id');
        }

        $item = $pmgwCollection->fetchItem();

        $orderId = $item->getOrderId();

        $this->order->loadByIncrementId($orderId);
        if (!$this->order->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException('Order not found.');
        }

        if (strpos($this->order->getPayment()->getMethodInstance()->getCode(), 'bigfish_pmgw_') !== 0) {
            throw new \Magento\Framework\Exception\LocalizedException('Unknown payment method.');
        }

        if($fullCheck) {
            if ($item->getStatus() != Helper::TRANSACTION_STATUS_STARTED)
            {
                throw new \Magento\Framework\Exception\LocalizedException('Invalid transaction state.');
            }
        }

        return $params;
    }

    protected function _setTransactionStatus($status)
    {
        $collection = $this->pmgwFactory->create()->getCollection()
                           ->addFieldToSelect('*')
                           ->addFieldToFilter('transaction_id',array('eq'=>$this->getEventData(Helper::TXN_ID)))
                           ->load();
        $item = $collection->fetchItem();
        $item->setStatus($status)
             ->save();
    }

    protected function _addTransactionLog($debug)
    {
        $collection=$this->paymentGatewayMysql4PaymentGatewayCollectionFactory->create()
                           ->addFieldToSelect('*')
                           ->addFieldToFilter('transaction_id',array('eq'=>$this->getEventData("TransactionId")))
                           ->load();
        $item = $collection->fetchItem();
        $status = $item->getStatus();
        $id = $item->getId();

        $pgwLog = $this->paymentGatewayLogCollection->create();
        $pgwLog->setPaymentgatewayId($id)
               ->setStatus($status)
               ->setCreatedTime(date("Y-m-d H:i:s"))
               ->setDebug($debug)
               ->save();
    }

    /**
     * Check customer authentication
     *
     * @param \BigFish\Pmgw\Gateway\Response\ResponseEvent $request
     *
     * @return mixed
     */
    public function dispatch(ResponseEvent $request)
    {
        if (!$request->isDispatched()) {
          return parent::dispatch($request);
        }
        if (!$this->_getSession()->authenticate()) {
          $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

}
