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
namespace BigFish\Pmgw\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender as OrderSenderFactory;

/**
 * Class EmailSender
 *
 * @package BigFish\Pmgw\Model\Order\Email\Sender
 */
class OrderSender extends OrderSenderFactory {

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param bool $forceSyncMode
     *
     * @param bool $forceSend
     *
     * @return bool
     */
    public function send(Order $order, $forceSyncMode = false, $forceSend = false)
    {
        $payment = $order->getPayment()->getMethodInstance()->getCode();

        if (strpos($payment, 'bigfish_pmgw_') === 0 && !$forceSend) {
            return false;
        }

        $order->setSendEmail(true);

        if ((!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) && $this->checkAndSend($order)) {
            $order->setEmailSent(true);
            $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
            return true;
        }

        $this->orderResource->saveAttribute($order, 'send_email');

        return false;
    }
}