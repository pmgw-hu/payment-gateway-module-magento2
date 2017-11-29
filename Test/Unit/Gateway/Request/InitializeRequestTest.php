<?php
namespace BigFish\Pmgw\Test\Unit\Gateway\Request;

use BigFish\PaymentGateway;
use BigFish\PaymentGateway\Request\Init as InitRequest;
use BigFish\PaymentGateway\Response;
use BigFish\Pmgw\Model\ConfigProvider;
use BigFish\Pmgw\Gateway\Request\InitializeRequest;
use BigFish\Pmgw\Gateway\Helper\Helper;
use BigFish\Pmgw\Test\Unit\Fixtures\StoreInterfaceFixture as StoreInterface;
use BigFish\Pmgw\Test\Unit\Fixtures\TransactionFixture as Transaction;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Psr\Log\LoggerInterface;

class InitializeRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $productMetaDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentDataObjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentMethodMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $transactionMock;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $date;

    public function setUp()
    {
        $this->configProviderMock = $this->getMockBuilder(ConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMock();

        $this->productMetaDataMock = $this->getMockBuilder(ProductMetadataInterface::class)
            ->getMock();

        $this->moduleListMock = $this->getMockBuilder(ModuleListInterface::class)
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentDataObjectMock = $this->getMockBuilder(PaymentDataObjectInterface::class)
            ->getMock();

        $this->paymentMock = $this->getMockBuilder(InfoInterface::class)
            ->getMock();

        $this->paymentMethodMock = $this->getMockBuilder(MethodInterface::class)
            ->getMock();

        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMock();

        $this->orderMock = $this->getMockBuilder(OrderAdapterInterface::class)
            ->getMock();

        $this->transactionMock = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionId = md5(rand(1, 100));
        $this->amount = rand(2000, 4000);
        $this->orderId = rand(1, 1000);
        $this->userId = rand(1, 1000);
        $this->date = date('Y-m-d H:i:s');
    }

    /**
     * @test
     */
    public function successfulBuildRequestTest()
    {
        $expectedInitRequest = $this->getExpectedInitRequest();

        $expectedInitRequest->providerName = 'test_provider_code';

        $response = new Response(json_encode([
            'ResultCode' => PaymentGateway::RESULT_CODE_SUCCESS,
            'TransactionId' => $this->transactionId,
        ]));

        $this->setSuccessfulRequestCommon($expectedInitRequest, $response);

        $this->configProviderMock->expects(static::once())
            ->method('getProviderConfig')
            ->with('bigfish_pmgw_test')
            ->will($this->returnValue([
                'name' => 'bigfish_pmgw_test',
                'storename' => 'test_storename',
                'apikey' => 'test_apikey',
                'provider_code' => 'test_provider_code',
                'response_url' => '/test_response_url',
                'one_click_payment' => 0,
                'testmode' => 1,
                'active' => 1,
                'debug' => 1,
            ]));

        $this->paymentMethodMock->expects(static::once())
            ->method('getCode')
            ->will($this->returnValue('bigfish_pmgw_test'));

        $initializeRequest = new InitializeRequest(
            $this->configProviderMock,
            $this->storeManagerMock,
            $this->productMetaDataMock,
            $this->moduleListMock,
            $this->helperMock,
            $this->loggerMock,
            $this->dateTimeMock
        );

        $this->assertEquals([
            'ResultCode' => 'SUCCESSFUL',
            'TransactionId' => $this->transactionId,
        ], $initializeRequest->build([
            'payment' => $this->paymentDataObjectMock,
        ]));

    }

    /**
     * @test
     */
    public function successfulBuildRequestWithOneClickPaymentTest()
    {
        $expectedInitRequest = $this->getExpectedInitRequest();

        $expectedInitRequest->providerName = 'test_provider_code';
        $expectedInitRequest->oneClickPayment = true;

        $response = new Response(json_encode([
            'ResultCode' => PaymentGateway::RESULT_CODE_SUCCESS,
            'TransactionId' => $this->transactionId,
        ]));

        $this->setSuccessfulRequestCommon($expectedInitRequest, $response);

        $this->configProviderMock->expects(static::once())
            ->method('getProviderConfig')
            ->with('bigfish_pmgw_test')
            ->will($this->returnValue([
                'name' => 'bigfish_pmgw_test',
                'storename' => 'test_storename',
                'apikey' => 'test_apikey',
                'provider_code' => 'test_provider_code',
                'response_url' => '/test_response_url',
                'one_click_payment' => 1,
                'testmode' => 1,
                'active' => 1,
                'debug' => 1,
            ]));

        $this->paymentMethodMock->expects(static::once())
            ->method('getCode')
            ->will($this->returnValue('bigfish_pmgw_test'));

        $initializeRequest = new InitializeRequest(
            $this->configProviderMock,
            $this->storeManagerMock,
            $this->productMetaDataMock,
            $this->moduleListMock,
            $this->helperMock,
            $this->loggerMock,
            $this->dateTimeMock
        );

        $this->assertEquals([
            'ResultCode' => 'SUCCESSFUL',
            'TransactionId' => $this->transactionId,
        ], $initializeRequest->build([
            'payment' => $this->paymentDataObjectMock,
        ]));

    }

    /**
     * @test
     */
    public function successfulBuildRequestWithKhbSzepProviderTest()
    {
        $expectedInitRequest = $this->getExpectedInitRequest();

        $expectedInitRequest->providerName = 'KHBSZEP';
        $expectedInitRequest->responseUrl = 'http://example.com/test_response_url';
        $expectedInitRequest->extra = $this->urlSafeEncode(json_encode(['KhbCardPocketId' => 'test_card_pocket_id']));

        unset($expectedInitRequest->otpCardPocketId);
        unset($expectedInitRequest->oneClickPayment);
        unset($expectedInitRequest->oneClickReferenceId);
        unset($expectedInitRequest->otpCardNumber);
        unset($expectedInitRequest->otpExpiration);
        unset($expectedInitRequest->otpCvc);
        unset($expectedInitRequest->otpConsumerRegistrationId);
        unset($expectedInitRequest->mkbSzepCardNumber);
        unset($expectedInitRequest->mkbSzepCvv);

        $response = new Response(json_encode([
            'ResultCode' => PaymentGateway::RESULT_CODE_SUCCESS,
            'TransactionId' => $this->transactionId,
        ]));

        $this->setSuccessfulRequestCommon($expectedInitRequest, $response);

        $this->configProviderMock->expects(static::once())
            ->method('getProviderConfig')
            ->with('bigfish_pmgw_khbszep')
            ->will($this->returnValue([
                'name' => 'bigfish_pmgw_khbszep',
                'storename' => 'test_storename',
                'apikey' => 'test_apikey',
                'provider_code' => 'KHBSZEP',
                'response_url' => '/test_response_url',
                'card_pocket_id' => 'test_card_pocket_id',
                'one_click_payment' => 0,
                'testmode' => 1,
                'active' => 1,
                'debug' => 1,
            ]));

        $this->paymentMethodMock->expects(static::once())
            ->method('getCode')
            ->will($this->returnValue('bigfish_pmgw_khbszep'));

        $initializeRequest = new InitializeRequest(
            $this->configProviderMock,
            $this->storeManagerMock,
            $this->productMetaDataMock,
            $this->moduleListMock,
            $this->helperMock,
            $this->loggerMock,
            $this->dateTimeMock
        );

        $this->assertEquals([
            'ResultCode' => 'SUCCESSFUL',
            'TransactionId' => $this->transactionId,
        ], $initializeRequest->build([
            'payment' => $this->paymentDataObjectMock,
        ]));

    }

    /**
     * @test
     */
    public function successfulBuildRequestWithMkbSzepProviderTest()
    {
        $expectedInitRequest = $this->getExpectedInitRequest();

        $expectedInitRequest->providerName = 'MKBSZEP';
        $expectedInitRequest->mkbSzepCafeteriaId = 10;
        $expectedInitRequest->gatewayPaymentPage = true;

        $response = new Response(json_encode([
            'ResultCode' => PaymentGateway::RESULT_CODE_SUCCESS,
            'TransactionId' => $this->transactionId,
        ]));

        $this->setSuccessfulRequestCommon($expectedInitRequest, $response);

        $this->configProviderMock->expects(static::once())
            ->method('getProviderConfig')
            ->with('bigfish_pmgw_mkbszep')
            ->will($this->returnValue([
                'name' => 'bigfish_pmgw_mkbszep',
                'storename' => 'test_storename',
                'apikey' => 'test_apikey',
                'provider_code' => 'MKBSZEP',
                'response_url' => '/test_response_url',
                'card_pocket_id' => 10,
                'one_click_payment' => 0,
                'testmode' => 1,
                'active' => 1,
                'debug' => 1,
            ]));

        $this->paymentMethodMock->expects(static::once())
            ->method('getCode')
            ->will($this->returnValue('bigfish_pmgw_mkbszep'));

        $initializeRequest = new InitializeRequest(
            $this->configProviderMock,
            $this->storeManagerMock,
            $this->productMetaDataMock,
            $this->moduleListMock,
            $this->helperMock,
            $this->loggerMock,
            $this->dateTimeMock
        );

        $this->assertEquals([
            'ResultCode' => 'SUCCESSFUL',
            'TransactionId' => $this->transactionId,
        ], $initializeRequest->build([
            'payment' => $this->paymentDataObjectMock,
        ]));

    }

    /**
     * @param InitRequest $expectedInitRequest
     * @param Response $response
     */
    private function setSuccessfulRequestCommon(InitRequest $expectedInitRequest, Response $response)
    {
        $this->productMetaDataMock->expects(static::once())
            ->method('getVersion')
            ->will($this->returnValue('test_magento_version'));

        $this->moduleListMock->expects(static::once())
            ->method('getOne')
            ->with('BigFish_Pmgw')
            ->will($this->returnValue([
                'setup_version' => 'test_setup_version',
            ]));

        $this->dateTimeMock->expects(static::once())
            ->method('date')
            ->will($this->returnValue($this->date));

        $this->paymentDataObjectMock->expects(static::once())
            ->method('getPayment')
            ->will($this->returnValue($this->paymentMock));

        $this->paymentDataObjectMock->expects(static::once())
            ->method('getOrder')
            ->will($this->returnValue($this->orderMock));

        $this->paymentMock->expects(static::once())
            ->method('getMethodInstance')
            ->will($this->returnValue($this->paymentMethodMock));

        $this->storeManagerMock->expects(static::any())
            ->method('getStore')
            ->will($this->returnValue($this->storeMock));

        $this->storeMock->expects(static::once())
            ->method('getBaseUrl')
            ->with('web')
            ->will($this->returnValue('http://example.com'));

        $this->storeMock->expects(static::once())
            ->method('getLocaleCode')
            ->will($this->returnValue('ts_TS'));

        $this->orderMock->expects(static::any())
            ->method('getOrderIncrementId')
            ->will($this->returnValue($this->orderId));

        $this->orderMock->expects(static::once())
            ->method('getGrandTotalAmount')
            ->will($this->returnValue($this->amount));

        $this->orderMock->expects(static::once())
            ->method('getCurrencyCode')
            ->will($this->returnValue('TES'));

        $this->orderMock->expects(static::once())
            ->method('getCustomerId')
            ->will($this->returnValue($this->userId));

        $this->transactionMock->expects(static::once())
            ->method('setOrderId')
            ->with($this->orderId)
            ->will($this->returnSelf());

        $this->transactionMock->expects(static::once())
            ->method('setTransactionId')
            ->with($this->transactionId)
            ->will($this->returnSelf());

        $this->transactionMock->expects(static::once())
            ->method('setCreatedTime')
            ->with($this->date)
            ->will($this->returnSelf());

        $this->transactionMock->expects(static::once())
            ->method('setStatus')
            ->with(100)
            ->will($this->returnSelf());

        $this->transactionMock->expects(static::once())
            ->method('save');

        $this->helperMock->expects(static::once())
            ->method('createTransaction')
            ->will($this->returnValue($this->transactionMock));

        $this->helperMock->expects(static::once())
            ->method('initializePaymentGatewayTransaction')
            ->with($expectedInitRequest)
            ->will($this->returnValue($response));

        $this->helperMock->expects(static::once())
            ->method('addTransactionLog')
            ->with($this->transactionMock, $response);

    }

    /**
     * @return InitRequest
     */
    private function getExpectedInitRequest()
    {
        $expectedRequest = new InitRequest();
        $expectedRequest->responseUrl = 'http://example.com/test_response_url';
        $expectedRequest->language = 'TS';
        $expectedRequest->amount = $this->amount;
        $expectedRequest->currency = 'TES';
        $expectedRequest->orderId = $this->orderId;
        $expectedRequest->userId = $this->userId;
        $expectedRequest->oneClickPayment = false;
        $expectedRequest->moduleName = 'Magento (test_magento_version)';
        $expectedRequest->moduleVersion = 'test_setup_version';
        $expectedRequest->autoCommit = 'true';

        return $expectedRequest;
    }

    /**
     * @param $string
     * @return string
     */
    private function urlSafeEncode($string)
    {
        $data = str_replace(array('+', '/', '='), array('-', '_', '.'), base64_encode($string));
        return $data;
    }

}
