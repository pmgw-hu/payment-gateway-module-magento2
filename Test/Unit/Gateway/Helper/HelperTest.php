<?php
namespace BigFish\Pmgw\Test\Unit\Gateway\Helper;

use BigFish\PaymentGateway\Config;
use BigFish\Pmgw\Gateway\Helper\Helper;
use BigFish\Pmgw\Model\TransactionFactory;
use BigFish\Pmgw\Model\LogFactory;
use BigFish\Pmgw\Test\Unit\Fixtures\Model\Transaction;
use BigFish\Pmgw\Test\Unit\Fixtures\Model\Log;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;

class HelperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentLoggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logMock;

    public function setUp()
    {
        parent::setUp();

        $this->transactionFactoryMock = $this->getMockBuilder(TransactionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logFactoryMock = $this->getMockBuilder(LogFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentLoggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->jsonHelperMock = $this->getMockBuilder(JsonHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logMock = $this->getMockBuilder(Log::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function createTransactionTest()
    {
        $this->transactionFactoryMock->expects(static::once())
            ->method('create')
            ->will($this->returnValue($this->transactionMock));

        $this->assertEquals(
            $this->transactionMock,
            $this->createHelper()->createTransaction()
        );
    }

    /**
     * @test
     */
    public function getTransactionByTransactionIdTest()
    {
        $this->transactionFactoryMock->expects(static::once())
            ->method('create')
            ->will($this->returnValue($this->transactionMock));

        $this->transactionMock->expects(static::once())
            ->method('load')
            ->with(1, 'transaction_id')
            ->will($this->returnSelf());

        $this->assertEquals(
            $this->transactionMock,
            $this->createHelper()->getTransactionByTransactionId(1)
        );
    }

    /**
     * @test
     */
    public function addTransactionLogTest()
    {
        $transactionId = 1;
        $status = 100;
        $date = date('Y-m-d H:i:s');
        $debug = ['foo', 'bar'];

        $this->transactionMock->expects(static::once())
            ->method('getId')
            ->will($this->returnValue($transactionId));

        $this->transactionMock->expects(static::once())
            ->method('getStatus')
            ->will($this->returnValue($status));

        $this->dateTimeMock->expects(static::once())
            ->method('date')
            ->will($this->returnValue($date));

        $this->jsonHelperMock->expects(static::once())
            ->method('jsonEncode')
            ->with($debug)
            ->will($this->returnValue(json_encode($debug)));

        $this->logFactoryMock->expects(static::once())
            ->method('create')
            ->will($this->returnValue($this->logMock));

        $this->logMock->expects(static::once())
            ->method('setPaymentgatewayId')
            ->with($transactionId)
            ->will($this->returnSelf());

        $this->logMock->expects(static::once())
            ->method('setStatus')
            ->with($status)
            ->will($this->returnSelf());

        $this->logMock->expects(static::once())
            ->method('setCreatedTime')
            ->with($date)
            ->will($this->returnSelf());

        $this->logMock->expects(static::once())
            ->method('setDebug')
            ->with(json_encode($debug))
            ->will($this->returnSelf());

        $this->logMock->expects(static::once())
            ->method('save');

        $this->createHelper()->addTransactionLog($this->transactionMock, $debug);
    }

    /**
     * @test
     */
    public function setPaymentGatewayConfigTest()
    {
        $config = $this->createPaymentGatewayConfig([
            'storeName' => 'test_storename',
            'apiKey' => 'test_apikey',
            'testMode' => true,
            'moduleName' => 'test_modulename',
            'moduleVersion' => 'test_moduleversion',
        ]);

        $this->paymentLoggerMock->expects(static::once())
            ->method('debug')
            ->with(
                [
                    'action' => 'setConfig',
                    'data' => [
                        'storeName' => 'test_storename',
                        'apiKey' => 'test_apikey',
                        'testMode' => true,
                        'moduleName' => 'test_modulename',
                        'moduleVersion' => 'test_moduleversion',
                    ]
                ]
            );

        $this->createHelper()->setPaymentGatewayConfig($config);
    }

    /**
     * @test
     */
    public function getPaymentGatewayStartUrlTest()
    {
        $this->createHelper()->setPaymentGatewayConfig(
            $this->createPaymentGatewayConfig([
                'storeName' => 'test_storename',
                'apiKey' => 'test_apikey',
                'testMode' => true,
            ])
        );

        $this->assertEquals(
            'https://test.paymentgateway.hu/Start?TransactionId=test_transaction_id',
            $this->createHelper()->getPaymentGatewayStartUrl('test_transaction_id')
        );
    }

    /**
     * @return Helper
     */
    private function createHelper()
    {
        $helper = new Helper(
            $this->transactionFactoryMock,
            $this->logFactoryMock,
            $this->paymentLoggerMock,
            $this->jsonHelperMock,
            $this->dateTimeMock
        );
        return $helper;
    }

    /**
     * @param array $data
     * @return Config
     */
    private function createPaymentGatewayConfig(array $data)
    {
        $config = new Config($data);
        return $config;
    }

}
