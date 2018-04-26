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
namespace Bigfishpaymentgateway\Pmgw\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @param bool $forceSyncMode
     * @return bool
     */
    public function send(Order $order, $forceSyncMode = false)
    {
        if (
            $this->isPaymentGatewayPayment($order) &&
            !in_array($order->getState(), [
                Order::STATE_PROCESSING,
                Order::STATE_COMPLETE,
                Order::STATE_CLOSED,
            ])
        ) {
            return false;
        }

        parent::send($order, $forceSyncMode);
    }

    /**
     * @param Order $order
     * @return bool
     */
    protected function isPaymentGatewayPayment(Order $order)
    {
        return (strpos($order->getPayment()->getMethod(), 'bigfishpaymentgateway_pmgw_') === 0);
    }

}
