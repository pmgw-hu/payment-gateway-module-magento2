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
namespace Bigfishpaymentgateway\Pmgw\Test\Unit\Gateway\Response;

use BigFish\PaymentGateway;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Bigfishpaymentgateway\Pmgw\Model\Response\Result;
use Psr\Log\LoggerInterface;
use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use Bigfishpaymentgateway\Pmgw\Gateway\Response\ResponseProcessor;
use BigFish\PaymentGateway\Response;
use Bigfishpaymentgateway\Pmgw\Model\Transaction;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class ResponseProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderSenderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    /**
     * @var Result
     */
    private $result;

    public function setUp()
    {
        parent::setUp();

        $this->paymentMock = $this->getMockBuilder(OrderPaymentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderSenderMock = $this->getMockBuilder(OrderSender::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->result = new Result();
    }

    /**
     * @test
     */
    public function missingTransactionIdFromResponseObjectTest()
    {
        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'ResultCode' => PaymentGateway::RESULT_CODE_PENDING,
        ]));

        $responseProcessor->setResponse($response);

        $responseProcessor->processResponse();

        $this->assertEquals('ERROR', $this->result->getCode());
        $this->assertEquals('Missing or invalid transaction id.', $this->result->getMessage());
    }

    /**
     * @test
     */
    public function missingResultCodeFromResponseObjectTest()
    {
        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'TransactionId' => 'test',
        ]));

        $responseProcessor->setResponse($response);

        $responseProcessor->processResponse();

        $this->assertEquals('ERROR', $this->result->getCode());
        $this->assertEquals('Missing or invalid result code.', $this->result->getMessage());
    }

    /**
     * @test
     */
    public function transactionNotFoundTest()
    {
        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'TransactionId' => 'test',
            'ResultCode' => PaymentGateway::RESULT_CODE_PENDING,
            'ResultMessage' => 'pending message',
        ]));

        $responseProcessor->setResponse($response);

        $responseProcessor->processResponse();

        $this->assertEquals('ERROR', $this->result->getCode());
        $this->assertEquals('Transaction not found.', $this->result->getMessage());
    }

    /**
     * @test
     */
    public function orderNotFoundTest()
    {
        $this->setTransactionMockGetId(1);
        $this->getHelperMockGetTransactionByTransactionId();

        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'TransactionId' => 'test',
            'ResultCode' => PaymentGateway::RESULT_CODE_PENDING,
            'ResultMessage' => 'pending message',
        ]));

        $responseProcessor->setResponse($response);

        $responseProcessor->processResponse();

        $this->assertEquals('ERROR', $this->result->getCode());
        $this->assertEquals('Order not found.', $this->result->getMessage());
    }

    /**
     * @test
     */
    public function invalidPaymentMethodTest()
    {
        $this->setOrderMockGetId(1);
        $this->setOrderMockGetPayment();
        $this->setTransactionMockGetId(1);
        $this->getHelperMockGetTransactionByTransactionId();

        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'TransactionId' => 'test',
            'ResultCode' => PaymentGateway::RESULT_CODE_PENDING,
            'ResultMessage' => 'pending message',
        ]));

        $responseProcessor->setResponse($response);

        $responseProcessor->processResponse();

        $this->assertEquals('ERROR', $this->result->getCode());
        $this->assertEquals('Invalid payment method.', $this->result->getMessage());
    }

    /**
     * @test
     */
    public function processPendingResponseTest()
    {
        $this->setPaymentMockGetMethod('bigfishpaymentgateway_pmgw_test');
        $this->setOrderMockGetId(1);
        $this->setOrderMockGetPayment();
        $this->setTransactionMockGetId(1);
        $this->getHelperMockGetTransactionByTransactionId();

        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'TransactionId' => 'test',
            'ResultCode' => PaymentGateway::RESULT_CODE_PENDING,
            'ResultMessage' => 'pending message',
        ]));

        $responseProcessor->setResponse($response);

        $this->paymentMock->expects(static::once())
            ->method('setLastTransId')
            ->with('test');

        $this->orderMock->expects(static::once())
            ->method('setState')
            ->with('pending_payment');

        $this->orderMock->expects(static::never())
            ->method('prepareInvoice');

        $this->orderSenderMock->expects(static::never())
            ->method('send');

        $this->helperMock->expects(static::once())
            ->method('updateTransactionStatus')
            ->with($this->transactionMock, 120);

        $this->helperMock->expects(static::once())
            ->method('addTransactionLog')
            ->with($this->transactionMock, $response);

        $responseProcessor->processResponse();

        $this->assertEquals('PENDING', $this->result->getCode());
        $this->assertEquals('pending message', $this->result->getMessage());
    }

    /**
     * @test
     */
    public function processSuccessResponseWithAnumTest()
    {
        $this->setPaymentMockGetMethod('bigfishpaymentgateway_pmgw_test');
        $this->setInvoiceMockRegister();
        $this->setOrderMockGetId(1);
        $this->setOrderMockCanInvoice(true);

        $this->setOrderMockPrepareInvoice();

        $this->setOrderMockGetPayment();
        $this->setTransactionMockGetId(1);
        $this->getHelperMockGetTransactionByTransactionId();

        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'TransactionId' => 'test',
            'ResultCode' => PaymentGateway::RESULT_CODE_SUCCESS,
            'ResultMessage' => 'success message',
            'Anum' => '123456',
        ]));

        $responseProcessor->setResponse($response);

        $this->paymentMock->expects(static::once())
            ->method('setLastTransId')
            ->with('test');

        $this->paymentMock->expects(static::once())
            ->method('setPoNumber')
            ->with('123456');

        $this->orderMock->expects(static::once())
            ->method('setState')
            ->with('processing');

        $this->orderMock->expects(static::once())
            ->method('prepareInvoice');

        $this->orderSenderMock->expects(static::once())
            ->method('send')
            ->with($this->orderMock, false);

        $this->helperMock->expects(static::once())
            ->method('updateTransactionStatus')
            ->with($this->transactionMock, 200);

        $this->helperMock->expects(static::once())
            ->method('addTransactionLog')
            ->with($this->transactionMock, $response);

        $responseProcessor->processResponse();

        $this->assertEquals('SUCCESSFUL', $this->result->getCode());
        $this->assertEquals('success message', $this->result->getMessage());
    }

    /**
     * @test
     */
    public function processSuccessResponseWithoutAnumTest()
    {
        $this->setPaymentMockGetMethod('bigfishpaymentgateway_pmgw_test');
        $this->setInvoiceMockRegister();
        $this->setOrderMockGetId(1);
        $this->setOrderMockGetPayment();
        $this->setOrderMockCanInvoice(true);
        $this->setOrderMockPrepareInvoice();
        $this->setTransactionMockGetId(1);
        $this->getHelperMockGetTransactionByTransactionId();

        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'TransactionId' => 'test',
            'ResultCode' => PaymentGateway::RESULT_CODE_SUCCESS,
            'ResultMessage' => 'success message',
        ]));

        $responseProcessor->setResponse($response);

        $this->paymentMock->expects(static::once())
            ->method('setLastTransId')
            ->with('test');

        $this->paymentMock->expects(static::never())
            ->method('setPoNumber');

        $this->orderMock->expects(static::once())
            ->method('setState')
            ->with('processing');

        $this->orderMock->expects(static::once())
            ->method('prepareInvoice');

        $this->orderSenderMock->expects(static::once())
            ->method('send')
            ->with($this->orderMock, false);

        $this->helperMock->expects(static::once())
            ->method('updateTransactionStatus')
            ->with($this->transactionMock, 200);

        $this->helperMock->expects(static::once())
            ->method('addTransactionLog')
            ->with($this->transactionMock, $response);

        $responseProcessor->processResponse();

        $this->assertEquals('SUCCESSFUL', $this->result->getCode());
        $this->assertEquals('success message', $this->result->getMessage());
    }

    /**
     * @test
     */
    public function processUserCancelResponseTest()
    {
        $this->setPaymentMockGetMethod('bigfishpaymentgateway_pmgw_test');
        $this->setOrderMockGetId(1);
        $this->setOrderMockGetPayment();
        $this->setTransactionMockGetId(1);
        $this->getHelperMockGetTransactionByTransactionId();

        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'TransactionId' => 'test',
            'ResultCode' => PaymentGateway::RESULT_CODE_USER_CANCEL,
            'ResultMessage' => 'user cancel message',
        ]));

        $responseProcessor->setResponse($response);

        $this->paymentMock->expects(static::once())
            ->method('setLastTransId')
            ->with('test');

        $this->orderMock->expects(static::once())
            ->method('cancel');

        $this->orderMock->expects(static::never())
            ->method('setState');

        $this->orderMock->expects(static::never())
            ->method('prepareInvoice');

        $this->orderSenderMock->expects(static::never())
            ->method('send');

        $this->helperMock->expects(static::once())
            ->method('updateTransactionStatus')
            ->with($this->transactionMock, 220);

        $this->helperMock->expects(static::once())
            ->method('addTransactionLog')
            ->with($this->transactionMock, $response);

        $responseProcessor->processResponse();

        $this->assertEquals('CANCELED', $this->result->getCode());
        $this->assertEquals('user cancel message', $this->result->getMessage());
    }

    /**
     * @test
     */
    public function processErrorResponseTest()
    {
        $this->setPaymentMockGetMethod('bigfishpaymentgateway_pmgw_test');
        $this->setOrderMockGetId(1);
        $this->setOrderMockGetPayment();
        $this->setTransactionMockGetId(1);
        $this->getHelperMockGetTransactionByTransactionId();

        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'TransactionId' => 'test',
            'ResultCode' => PaymentGateway::RESULT_CODE_ERROR,
            'ResultMessage' => 'error message',
        ]));

        $responseProcessor->setResponse($response);

        $this->paymentMock->expects(static::once())
            ->method('setLastTransId')
            ->with('test');

        $this->orderMock->expects(static::once())
            ->method('cancel');

        $this->orderMock->expects(static::never())
            ->method('setState');

        $this->orderMock->expects(static::never())
            ->method('prepareInvoice');

        $this->orderSenderMock->expects(static::never())
            ->method('send');

        $this->helperMock->expects(static::once())
            ->method('updateTransactionStatus')
            ->with($this->transactionMock, 210);

        $this->helperMock->expects(static::once())
            ->method('addTransactionLog')
            ->with($this->transactionMock, $response);

        $responseProcessor->processResponse();

        $this->assertEquals('ERROR', $this->result->getCode());
        $this->assertEquals('error message', $this->result->getMessage());
    }

    /**
     * @test
     */
    public function processTimeoutResponseTest()
    {
        $this->setPaymentMockGetMethod('bigfishpaymentgateway_pmgw_test');
        $this->setOrderMockGetId(1);
        $this->setOrderMockGetPayment();
        $this->setTransactionMockGetId(1);
        $this->getHelperMockGetTransactionByTransactionId();

        $responseProcessor = new ResponseProcessor(
            $this->orderMock,
            $this->orderSenderMock,
            $this->result,
            $this->loggerMock,
            $this->helperMock
        );

        $response = new Response(json_encode([
            'TransactionId' => 'test',
            'ResultCode' => PaymentGateway::RESULT_CODE_TIMEOUT,
            'ResultMessage' => 'timeout message',
        ]));

        $responseProcessor->setResponse($response);

        $this->paymentMock->expects(static::once())
            ->method('setLastTransId')
            ->with('test');

        $this->orderMock->expects(static::once())
            ->method('cancel');

        $this->orderMock->expects(static::never())
            ->method('setState');

        $this->orderMock->expects(static::never())
            ->method('prepareInvoice');

        $this->orderSenderMock->expects(static::never())
            ->method('send');

        $this->helperMock->expects(static::once())
            ->method('updateTransactionStatus')
            ->with($this->transactionMock, 210);

        $this->helperMock->expects(static::once())
            ->method('addTransactionLog')
            ->with($this->transactionMock, $response);

        $responseProcessor->processResponse();

        $this->assertEquals('TIMEOUT', $this->result->getCode());
        $this->assertEquals('timeout message', $this->result->getMessage());
    }

    /**
     * @param string $method
     */
    protected function setPaymentMockGetMethod($method)
    {
        $this->paymentMock->expects(static::any())
            ->method('getMethod')
            ->will($this->returnValue($method));
    }

    /**
     * @param integer $id
     */
    protected function setOrderMockGetId($id)
    {
        $this->orderMock->expects(static::any())
            ->method('getId')
            ->will($this->returnValue($id));
    }

    protected function setOrderMockGetPayment()
    {
        $this->orderMock->expects(static::any())
            ->method('getPayment')
            ->will($this->returnValue($this->paymentMock));
    }

    /**
     * @param int $id
     */
    protected function setTransactionMockGetId($id)
    {
        $this->transactionMock->expects(static::any())
            ->method('getId')
            ->will($this->returnValue($id));
    }

    protected function getHelperMockGetTransactionByTransactionId()
    {
        $this->helperMock->expects(static::any())
            ->method('getTransactionByTransactionId')
            ->will($this->returnValue($this->transactionMock));
    }

    protected function setInvoiceMockRegister()
    {
        $this->invoiceMock->expects(static::any())
            ->method('register')
            ->will($this->returnSelf());
    }

    /**
     * @param $canInvoice
     */
    protected function setOrderMockCanInvoice($canInvoice)
    {
        $this->orderMock->expects(static::any())
            ->method('canInvoice')
            ->will($this->returnValue($canInvoice));
    }

    protected function setOrderMockPrepareInvoice()
    {
        $this->orderMock->expects(static::any())
            ->method('prepareInvoice')
            ->will($this->returnValue($this->invoiceMock));
    }

}
