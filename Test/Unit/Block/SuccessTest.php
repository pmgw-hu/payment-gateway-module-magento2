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
namespace Bigfishpaymentgateway\Pmgw\Test\Unit\Block;

use Bigfishpaymentgateway\Pmgw\Test\Unit\Fixtures\Block\Success;
use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use Bigfishpaymentgateway\Pmgw\Model\Transaction;
use Bigfishpaymentgateway\Pmgw\Model\Log;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class SuccessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $httpContextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderPaymentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionLogMock;

    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderConfigMock = $this->getMockBuilder(OrderConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->httpContextMock = $this->getMockBuilder(HttpContext::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonHelperMock = $this->getMockBuilder(JsonHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderPaymentMock = $this->getMockBuilder(OrderPaymentInterface::class)
            ->getMock();

        $this->transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionLogMock = $this->getMockBuilder(Log::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function createBlockAfterPaymentWithLoggedInUserTest()
    {
        $block = new Success(
            $this->contextMock,
            $this->checkoutSessionMock,
            $this->orderConfigMock,
            $this->httpContextMock,
            $this->helperMock,
            $this->jsonHelperMock,
            []
        );

        $result = [
            'ResultMessage' => 'test_result_message',
            'ProviderTransactionId' => 'test_provider_transaction_id',
            'Anum' => 'test_anum',
        ];

        $this->preparePayment($result, 1);

        $this->assertEquals($block, $block->_beforeToHtml());

        $this->assertEquals([
            'order_id' => '0000001',
            'can_view_order' => true,
            'response' => (object)$result,
        ], $block->getData());
    }

    /**
     * @test
     */
    public function createBlockAfterPaymentWithGuestUserTest()
    {
        $block = new Success(
            $this->contextMock,
            $this->checkoutSessionMock,
            $this->orderConfigMock,
            $this->httpContextMock,
            $this->helperMock,
            $this->jsonHelperMock,
            []
        );

        $result = [
            'ResultMessage' => 'test_result_message',
            'ProviderTransactionId' => 'test_provider_transaction_id',
            'Anum' => 'test_anum',
        ];

        $this->preparePayment($result, 0);

        $this->assertEquals($block, $block->_beforeToHtml());

        $this->assertEquals([
            'order_id' => '0000001',
            'can_view_order' => false,
            'response' => (object)$result,
        ], $block->getData());
    }

    /**
     * @test
     */
    public function invalidOrderTest()
    {
        $block = new Success(
            $this->contextMock,
            $this->checkoutSessionMock,
            $this->orderConfigMock,
            $this->httpContextMock,
            $this->helperMock,
            $this->jsonHelperMock,
            []
        );

        $this->setCheckoutSessionMockGetLastRealOrder(null);

        $this->assertEquals($block, $block->_beforeToHtml());

        $this->assertEquals([], $block->getData());
    }

    /**
     * @test
     */
    public function invalidOrderPaymentTest()
    {
        $block = new Success(
            $this->contextMock,
            $this->checkoutSessionMock,
            $this->orderConfigMock,
            $this->httpContextMock,
            $this->helperMock,
            $this->jsonHelperMock,
            []
        );

        $this->setCheckoutSessionMockGetLastRealOrder($this->orderMock);
        $this->setOrderMockGetId();
        $this->setOrderMockGetPayment(null);

        $this->assertEquals($block, $block->_beforeToHtml());

        $this->assertEquals([], $block->getData());
    }

    /**
     * @test
     */
    public function invalidTransactionIdTest()
    {
        $block = new Success(
            $this->contextMock,
            $this->checkoutSessionMock,
            $this->orderConfigMock,
            $this->httpContextMock,
            $this->helperMock,
            $this->jsonHelperMock,
            []
        );

        $this->setCheckoutSessionMockGetLastRealOrder($this->orderMock);
        $this->setOrderMockGetId();
        $this->setOrderMockGetPayment($this->orderPaymentMock);
        $this->setOrderPaymentMockGetLastTransId(null);

        $this->assertEquals($block, $block->_beforeToHtml());

        $this->assertEquals([], $block->getData());
    }

    /**
     * @test
     */
    public function invalidTransactionTest()
    {
        $block = new Success(
            $this->contextMock,
            $this->checkoutSessionMock,
            $this->orderConfigMock,
            $this->httpContextMock,
            $this->helperMock,
            $this->jsonHelperMock,
            []
        );

        $this->setCheckoutSessionMockGetLastRealOrder($this->orderMock);
        $this->setOrderMockGetId();
        $this->setOrderMockGetPayment($this->orderPaymentMock);
        $this->setOrderPaymentMockGetLastTransId('test_transaction_id');
        $this->setOrderMockGetIncementId();
        $this->setHttpContextMockGetValueCustomerLoggedIn(1);
        $this->setOrderMockGetStatus();
        $this->getOrderConfigMockGetInvisibleOnFrontStatuses();
        $this->setHelperMockGetTransactionByTransactionId('test_transaction_id', null);

        $this->assertEquals($block, $block->_beforeToHtml());

        $this->assertEquals([
            'order_id' => '0000001',
            'can_view_order' => true,
            'response' => null,
        ], $block->getData());
    }

    /**
     * @test
     */
    public function invalidTransactionLogTest()
    {
        $block = new Success(
            $this->contextMock,
            $this->checkoutSessionMock,
            $this->orderConfigMock,
            $this->httpContextMock,
            $this->helperMock,
            $this->jsonHelperMock,
            []
        );

        $this->setCheckoutSessionMockGetLastRealOrder($this->orderMock);
        $this->setOrderMockGetId();
        $this->setOrderMockGetPayment($this->orderPaymentMock);
        $this->setOrderPaymentMockGetLastTransId('test_transaction_id');
        $this->setOrderMockGetIncementId();
        $this->setHttpContextMockGetValueCustomerLoggedIn(1);
        $this->setOrderMockGetStatus();
        $this->getOrderConfigMockGetInvisibleOnFrontStatuses();
        $this->setHelperMockGetTransactionByTransactionId('test_transaction_id', $this->transactionMock);
        $this->setTransactionMockGetId();
        $this->setHelperMockGetTransactionLog($this->transactionMock, null);

        $this->assertEquals($block, $block->_beforeToHtml());

        $this->assertEquals([
            'order_id' => '0000001',
            'can_view_order' => true,
            'response' => null,
        ], $block->getData());
    }

    /**
     * @param array $result
     * @param int $isLoggedIn
     */
    private function preparePayment(array $result, $isLoggedIn)
    {
        $this->setCheckoutSessionMockGetLastRealOrder($this->orderMock);
        $this->setOrderMockGetId();
        $this->setOrderMockGetPayment($this->orderPaymentMock);
        $this->setOrderPaymentMockGetLastTransId('test_transaction_id');
        $this->setOrderMockGetIncementId();
        $this->setHttpContextMockGetValueCustomerLoggedIn($isLoggedIn);

        if ($isLoggedIn) {
            $this->setOrderMockGetStatus();
            $this->getOrderConfigMockGetInvisibleOnFrontStatuses();
        }

        $this->setHelperMockGetTransactionByTransactionId('test_transaction_id', $this->transactionMock);
        $this->setTransactionMockGetId();
        $this->setHelperMockGetTransactionLog($this->transactionMock, $this->transactionLogMock);
        $this->setTransactionLogMockGetId();
        $this->setTransactionLogMockGetData($result);
        $this->setJsonHelperMockJsonDecode($result);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject|null $orderMock
     */
    private function setCheckoutSessionMockGetLastRealOrder(
        \PHPUnit_Framework_MockObject_MockObject $orderMock = null
    ) {
        $this->checkoutSessionMock->expects(static::once())
            ->method('getLastRealOrder')
            ->will($this->returnValue($orderMock));
    }

    private function setOrderMockGetId()
    {
        $this->orderMock->expects(static::once())
            ->method('getId')
            ->will($this->returnValue(1));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject|null $payment
     */
    private function setOrderMockGetPayment(
        \PHPUnit_Framework_MockObject_MockObject $payment = null
    ) {
        $this->orderMock->expects(static::once())
            ->method('getPayment')
            ->will($this->returnValue($payment));
    }

    /**
     * @param $transactionId
     */
    private function setOrderPaymentMockGetLastTransId($transactionId)
    {
        $this->orderPaymentMock->expects(static::once())
            ->method('getLastTransId')
            ->will($this->returnValue($transactionId));
    }

    private function setOrderMockGetIncementId()
    {
        $this->orderMock->expects(static::once())
            ->method('getIncrementId')
            ->will($this->returnValue('0000001'));
    }

    /**
     * @param $isLoggedIn
     */
    private function setHttpContextMockGetValueCustomerLoggedIn($isLoggedIn)
    {
        $this->httpContextMock->expects(static::once())
            ->method('getValue')
            ->with('customer_logged_in')
            ->will($this->returnValue($isLoggedIn));
    }

    private function setOrderMockGetStatus()
    {
        $this->orderMock->expects(static::once())
            ->method('getStatus')
            ->will($this->returnValue(100));
    }

    private function getOrderConfigMockGetInvisibleOnFrontStatuses()
    {
        $this->orderConfigMock->expects(static::once())
            ->method('getInvisibleOnFrontStatuses')
            ->will($this->returnValue([]));
    }

    /**
     * @param string|null $transactionId
     * @param \PHPUnit_Framework_MockObject_MockObject|null $transaction
     */
    private function setHelperMockGetTransactionByTransactionId(
        $transactionId,
        \PHPUnit_Framework_MockObject_MockObject $transaction = null
    ) {
        $this->helperMock->expects(static::once())
            ->method('getTransactionByTransactionId')
            ->with($transactionId)
            ->will($this->returnValue($transaction));
    }

    private function setTransactionMockGetId()
    {
        $this->transactionMock->expects(static::once())
            ->method('getId')
            ->will($this->returnValue(1000));
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject|null $transactionMock
     * @param \PHPUnit_Framework_MockObject_MockObject|null $transactionLogMock
     */
    private function setHelperMockGetTransactionLog(
        \PHPUnit_Framework_MockObject_MockObject $transactionMock = null,
        \PHPUnit_Framework_MockObject_MockObject $transactionLogMock = null
    ) {
        $this->helperMock->expects(static::once())
            ->method('getTransactionLog')
            ->with($transactionMock)
            ->will($this->returnValue($transactionLogMock));
    }

    private function setTransactionLogMockGetId()
    {
        $this->transactionLogMock->expects(static::once())
            ->method('getId')
            ->will($this->returnValue(2000));
    }

    /**
     * @param array $result
     */
    private function setTransactionLogMockGetData(array $result)
    {
        $this->transactionLogMock->expects(static::once())
            ->method('getData')
            ->will($this->returnValue(json_encode($result)));
    }

    /**
     * @param array $result
     */
    private function setJsonHelperMockJsonDecode(array $result)
    {
        $this->jsonHelperMock->expects(static::once())
            ->method('jsonDecode')
            ->with(json_encode($result))
            ->will($this->returnValue($result));
    }

}
