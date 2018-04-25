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
namespace Bigfishpaymentgateway\Pmgw\Test\Unit\Model\Order\Email\Sender;

use Bigfishpaymentgateway\Pmgw\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\SenderBuilderFactory;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class OrderSenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function skipSendEmailAfterPlaceOrderTest()
    {
        $templateContainerMock = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();

        $identityContainerMock = $this->getMockBuilder(OrderIdentity::class)
            ->disableOriginalConstructor()
            ->getMock();

        $senderBuilderFactoryMock = $this->getMockBuilder(SenderBuilderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $addressRendererMock = $this->getMockBuilder(Renderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentHelperMock = $this->getMockBuilder(PaymentHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderResourceMock = $this->getMockBuilder(OrderResource::class)
            ->disableOriginalConstructor()
            ->getMock();

        $globalConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMock();

        $paymentMock = $this->getMockBuilder(OrderPaymentInterface::class)
            ->getMock();

        $paymentMock->expects(static::any())
            ->method('getMethod')
            ->will($this->returnValue('bigfishpaymentgateway_pmgw_test'));

        $orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock->expects(static::any())
            ->method('getPayment')
            ->will($this->returnValue($paymentMock));

        $orderSender = new OrderSender(
            $templateContainerMock,
            $identityContainerMock,
            $senderBuilderFactoryMock,
            $loggerMock,
            $addressRendererMock,
            $paymentHelperMock,
            $orderResourceMock,
            $globalConfigMock,
            $eventManagerMock
        );

        $this->assertEquals(false, $orderSender->send($orderMock));
    }

}
