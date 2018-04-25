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
namespace Bigfishpaymentgateway\Pmgw\Test\Unit\Controller\Payment;

use BigFish\PaymentGateway;
use BigFish\PaymentGateway\Config;
use BigFish\PaymentGateway\Response as PaymentGatewayResponse;
use Bigfishpaymentgateway\Pmgw\Gateway\Response\ResponseProcessor;
use Bigfishpaymentgateway\Pmgw\Gateway\Helper\Helper;
use Bigfishpaymentgateway\Pmgw\Controller\Payment\Response as ResponseController;
use Bigfishpaymentgateway\Pmgw\Model\Response\ResultInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;

class ResponseTest extends AbstractTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseProcessorMock;

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
    private $resultMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    public function setUp()
    {
        parent::setUp();

        $this->responseProcessorMock = $this->getMockBuilder(ResponseProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->getMock();

        $this->redirectMock = $this->getMockBuilder(RedirectInterface::class)
            ->getMock();

        $this->contextMock->expects(static::any())
            ->method('getRedirect')
            ->will($this->returnValue($this->redirectMock));
    }

    /**
     * @test
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Missing or invalid transaction id.
     */
    public function missingTransactionIdTest()
    {
        $this->requestMock->expects(static::once())
            ->method('getParams')
            ->will($this->returnValue([]));

        $controller = new ResponseController(
            $this->contextMock,
            $this->responseProcessorMock,
            $this->configMock,
            $this->helperMock
        );

        $controller->execute();
    }

    /**
     * @test
     */
    public function pendingTransactionTest()
    {
        $response = $this->createPaymentGatewayResponse([
            'ResultCode' => PaymentGateway::RESULT_CODE_PENDING,
        ]);

        $resultCode = 'PENDING';
        $expectedRedirectUrl = 'checkout/onepage/success';
        $expectedRedirectParams = ['_secure' => true];

        $this->setupTransactionTest($response, $resultCode, $expectedRedirectUrl, $expectedRedirectParams);

        $controller = new ResponseController(
            $this->contextMock,
            $this->responseProcessorMock,
            $this->configMock,
            $this->helperMock
        );

        $controller->execute();
    }

    /**
     * @test
     */
    public function successfulTransactionTest()
    {
        $response = $this->createPaymentGatewayResponse([
            'ResultCode' => PaymentGateway::RESULT_CODE_SUCCESS,
        ]);

        $resultCode = 'SUCCESSFUL';
        $expectedRedirectUrl = 'checkout/onepage/success';
        $expectedRedirectParams = ['_secure' => true];

        $this->setupTransactionTest($response, $resultCode, $expectedRedirectUrl, $expectedRedirectParams);

        $controller = new ResponseController(
            $this->contextMock,
            $this->responseProcessorMock,
            $this->configMock,
            $this->helperMock
        );

        $controller->execute();
    }

    /**
     * @test
     */
    public function timeoutTransactionTest()
    {
        $response = $this->createPaymentGatewayResponse([
            'ResultCode' => PaymentGateway::RESULT_CODE_TIMEOUT,
        ]);

        $resultCode = 'TIMEOUT';
        $expectedRedirectUrl = 'checkout/onepage/failure';
        $expectedRedirectParams = ['_secure' => true];

        $this->setupTransactionTest($response, $resultCode, $expectedRedirectUrl, $expectedRedirectParams);

        $controller = new ResponseController(
            $this->contextMock,
            $this->responseProcessorMock,
            $this->configMock,
            $this->helperMock
        );

        $controller->execute();
    }

    /**
     * @test
     */
    public function errorTransactionTest()
    {
        $response = $this->createPaymentGatewayResponse([
            'ResultCode' => PaymentGateway::RESULT_CODE_ERROR,
        ]);

        $resultCode = 'ERROR';
        $expectedRedirectUrl = 'checkout/onepage/failure';
        $expectedRedirectParams = ['_secure' => true];

        $this->setupTransactionTest($response, $resultCode, $expectedRedirectUrl, $expectedRedirectParams);

        $controller = new ResponseController(
            $this->contextMock,
            $this->responseProcessorMock,
            $this->configMock,
            $this->helperMock
        );

        $controller->execute();
    }

    /**
     * @test
     */
    public function userCancelTransactionTest()
    {
        $response = $this->createPaymentGatewayResponse([
            'ResultCode' => PaymentGateway::RESULT_CODE_USER_CANCEL,
        ]);

        $resultCode = 'CANCELED';
        $expectedRedirectUrl = 'checkout/onepage/failure';
        $expectedRedirectParams = ['_secure' => true];

        $this->setupTransactionTest($response, $resultCode, $expectedRedirectUrl, $expectedRedirectParams);

        $controller = new ResponseController(
            $this->contextMock,
            $this->responseProcessorMock,
            $this->configMock,
            $this->helperMock
        );

        $controller->execute();
    }

    /**
     * @test
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Missing or invalid result code.
     */
    public function invalidResultCodeTest()
    {
        $response = $this->createPaymentGatewayResponse([
            'ResultCode' => PaymentGateway::RESULT_CODE_OPEN,
        ]);

        $resultCode = 'OPEN';

        $this->setRequestMockGetParams();

        $this->setConfigMockGetValue();

        $this->setHelperMockSetPaymentGatewayConfig();

        $this->setHelperMockGetPaymentGatewayResult($response);

        $this->setResponseProcessorMockSetResponse($response);

        $this->setResponseProcessorMockProcessResponse();

        $this->setResultMockGetCode($resultCode);

        $controller = new ResponseController(
            $this->contextMock,
            $this->responseProcessorMock,
            $this->configMock,
            $this->helperMock
        );

        $controller->execute();
    }

    /**
     * @param array $data
     * @return Config
     */
    private function createPaymentGatewayConfig(array $data)
    {
        return new Config($data);
    }

    /**
     * @param array $data
     * @return PaymentGatewayResponse
     */
    private function createPaymentGatewayResponse(array $data)
    {
        return new PaymentGatewayResponse(json_encode($data));
    }

    private function setRequestMockGetParams()
    {
        $this->requestMock->expects(static::once())
            ->method('getParams')
            ->will($this->returnValue([
                'TransactionId' => 'test_transaction_id',
            ]));
    }

    private function setConfigMockGetValue()
    {
        $this->configMock->expects(static::at(0))
            ->method('getValue')
            ->with('storename')
            ->will($this->returnValue('test_storename'));

        $this->configMock->expects(static::at(1))
            ->method('getValue')
            ->with('apikey')
            ->will($this->returnValue('test_apikey'));

        $this->configMock->expects(static::at(2))
            ->method('getValue')
            ->with('testmode')
            ->will($this->returnValue(1));
    }

    private function setHelperMockSetPaymentGatewayConfig()
    {
        $this->helperMock->expects(static::once())
            ->method('setPaymentGatewayConfig')
            ->with($this->createPaymentGatewayConfig([
                'storeName' => 'test_storename',
                'apiKey' => 'test_apikey',
            ]));
    }

    /**
     * @param $response
     */
    private function setHelperMockGetPaymentGatewayResult($response)
    {
        $this->helperMock->expects(static::once())
            ->method('getPaymentGatewayResult')
            ->with('test_transaction_id')
            ->will($this->returnValue($response));
    }

    /**
     * @param $response
     */
    private function setResponseProcessorMockSetResponse($response)
    {
        $this->responseProcessorMock->expects(static::once())
            ->method('setResponse')
            ->with($response);
    }

    private function setResponseProcessorMockProcessResponse()
    {
        $this->responseProcessorMock->expects(static::once())
            ->method('processResponse')
            ->will($this->returnValue($this->resultMock));
    }

    /**
     * @param $resultCode
     */
    private function setResultMockGetCode($resultCode)
    {
        $this->resultMock->expects(static::once())
            ->method('getCode')
            ->will($this->returnValue($resultCode));
    }

    /**
     * @param $expectedRedirectUrl
     * @param $expectedRedirectParams
     */
    private function setRedirectMockRedirect($expectedRedirectUrl, $expectedRedirectParams)
    {
        $this->redirectMock->expects(static::once())
            ->method('redirect')
            ->with($this->responseMock, $expectedRedirectUrl, $expectedRedirectParams);
    }

    /**
     * @param PaymentGatewayResponse $response
     * @param string $resultCode
     * @param string $expectedRedirectUrl
     * @param array $expectedRedirectParams
     */
    private function setupTransactionTest(
        PaymentGatewayResponse $response,
        $resultCode,
        $expectedRedirectUrl,
        array $expectedRedirectParams
    ) {
        $this->setRequestMockGetParams();

        $this->setConfigMockGetValue();

        $this->setHelperMockSetPaymentGatewayConfig();

        $this->setHelperMockGetPaymentGatewayResult($response);

        $this->setResponseProcessorMockSetResponse($response);

        $this->setResponseProcessorMockProcessResponse();

        $this->setResultMockGetCode($resultCode);

        $this->setRedirectMockRedirect($expectedRedirectUrl, $expectedRedirectParams);
    }

}
