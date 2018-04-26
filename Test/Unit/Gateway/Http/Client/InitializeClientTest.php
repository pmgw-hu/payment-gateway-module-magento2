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
namespace Bigfishpaymentgateway\Pmgw\Test\Unit\Gateway\Http\Clent;

use BigFish\PaymentGateway;
use BigFish\PaymentGateway\Config;
use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use Bigfishpaymentgateway\Pmgw\Gateway\Http\Client\InitializeClient;
use Bigfishpaymentgateway\Pmgw\Model\Transaction;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Payment\Gateway\Http\TransferInterface;

class InitializeClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $transferObjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionMock;

    public function setUp()
    {
        parent::setUp();

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transferObjectMock = $this->getMockBuilder(TransferInterface::class)
            ->getMock();

        $this->transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function successfulInitializationTest()
    {
        $this->transferObjectMock->expects(static::once())
            ->method('getBody')
            ->will($this->returnValue([
                Helper::RESPONSE_FIELD_RESULT_CODE => PaymentGateway::RESULT_CODE_SUCCESS,
                Helper::RESPONSE_FIELD_TRANSACTION_ID => 'test_transaction_id',
            ]));

        $this->configMock->expects(static::at(0))
            ->method('getValue')
            ->with('storename')
            ->will($this->returnValue('test_store'));

        $this->configMock->expects(static::at(1))
            ->method('getValue')
            ->with('apikey')
            ->will($this->returnValue('test_api_key'));

        $this->configMock->expects(static::at(2))
            ->method('getValue')
            ->with('testmode')
            ->will($this->returnValue('1'));

        $this->helperMock->expects(static::once())
            ->method('setPaymentGatewayConfig')
            ->with(new Config([
                'storeName' => 'test_store',
                'apiKey' => 'test_api_key',
                'testMode' => true,
            ]));

        $this->helperMock->expects(static::once())
            ->method('getPaymentGatewayStartUrl')
            ->with('test_transaction_id')
            ->will($this->returnValue('http://exaple.com/test_transaction_id'));

        $this->helperMock->expects(static::once())
            ->method('getTransactionByTransactionId')
            ->with('test_transaction_id')
            ->will($this->returnValue($this->transactionMock));

        $this->helperMock->expects(static::once())
            ->method('updateTransactionStatus')
            ->with($this->transactionMock, 110);

        $this->helperMock->expects(static::once())
            ->method('addTransactionLog')
            ->with($this->transactionMock, ['startUrl' => 'http://exaple.com/test_transaction_id']);

        $client = new InitializeClient(
            $this->configMock,
            $this->helperMock,
            $this->customerSessionMock
        );

        $this->assertEquals([
            'ResultCode' => 'SUCCESSFUL',
            'TransactionId' => 'test_transaction_id',
        ], $client->placeRequest($this->transferObjectMock));
    }

    /**
     * @test
     */
    public function unsuccessfulInitializationTest()
    {
        $this->transferObjectMock->expects(static::once())
            ->method('getBody')
            ->will($this->returnValue([
                Helper::RESPONSE_FIELD_RESULT_CODE => PaymentGateway::RESULT_CODE_ERROR,
            ]));

        $this->configMock->expects(static::never())
            ->method('getValue');

        $this->helperMock->expects(static::never())
            ->method('setPaymentGatewayConfig');

        $this->helperMock->expects(static::never())
            ->method('getPaymentGatewayStartUrl');

        $this->helperMock->expects(static::never())
            ->method('getTransactionByTransactionId');

        $this->helperMock->expects(static::never())
            ->method('updateTransactionStatus');

        $this->helperMock->expects(static::never())
            ->method('addTransactionLog');

        $client = new InitializeClient(
            $this->configMock,
            $this->helperMock,
            $this->customerSessionMock
        );

        $this->assertEquals([
            'ResultCode' => 'ERROR',
        ], $client->placeRequest($this->transferObjectMock));
    }

}
