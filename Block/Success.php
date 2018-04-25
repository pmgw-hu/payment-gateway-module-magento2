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
namespace Bigfishpaymentgateway\Pmgw\Block;

use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use Bigfishpaymentgateway\Pmgw\Model\Log;
use Bigfishpaymentgateway\Pmgw\Model\Transaction;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Customer\Model\Context as CutomerModelContext;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Success extends Template
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderConfig
     */
    private $orderConfig;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param OrderConfig $orderConfig
     * @param HttpContext $httpContext
     * @param Helper $helper
     * @param JsonHelper $jsonHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderConfig $orderConfig,
        HttpContext $httpContext,
        Helper $helper,
        JsonHelper $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->checkoutSession = $checkoutSession;
        $this->orderConfig = $orderConfig;
        $this->httpContext = $httpContext;
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;

        $this->_isScopePrivate = true;
    }

    /**
     * @return Template
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockData();
        return parent::_beforeToHtml();
    }

    protected function prepareBlockData()
    {
        /** @var Order $order */
        $order = $this->checkoutSession->getLastRealOrder();

        if (!$order || !$order->getId()) {
            return;
        }

        $transactionId = $this->getTransactionId($order);

        if (!$transactionId) {
            return;
        }

        $this->addData([
            'order_id'  => $order->getIncrementId(),
            'can_view_order' => $this->canViewOrder($order),
            'response' => $this->getTransactionData($transactionId),
        ]);
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function canViewOrder(Order $order)
    {
        return $this->httpContext->getValue(CutomerModelContext::CONTEXT_AUTH)
            && $this->isVisible($order);
    }

    /**
     * @param Order $order
     * @return bool
     */
    private function isVisible(Order $order)
    {
        return !in_array(
            $order->getStatus(),
            $this->orderConfig->getInvisibleOnFrontStatuses()
        );
    }

    /**
     * @param Order $order
     * @return null|string
     */
    private function getTransactionId(Order $order)
    {
        /** @var OrderPaymentInterface $payment */
        $payment = $order->getPayment();

        if (!$payment) {
            return null;
        }
        return $payment->getLastTransId();
    }

    /**
     * @param $transactionId
     * @return object|null
     */
    private function getTransactionData($transactionId)
    {
        /** @var Transaction $transaction */
        $transaction = $this->helper->getTransactionByTransactionId($transactionId);

        if (!$transaction || !$transaction->getId()) {
            return null;
        }

        /** @var Log $transactionLog */
        $transactionLog = $this->helper->getTransactionLog($transaction);

        if (!$transactionLog || !$transactionLog->getId()) {
            return null;
        }

        try {
            return (object)$this->jsonHelper->jsonDecode($transactionLog->getData('debug'));
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return null;
    }

}
