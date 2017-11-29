<?php
namespace BigFish\Pmgw\Test\Unit\Controller\Payment;

use BigFish\Pmgw\Test\Unit\Fixtures\SessionFixture as Session;
use BigFish\Pmgw\Test\Unit\Fixtures\ResultInterfaceFixture as ResultInterface;
use BigFish\Pmgw\Controller\Payment\Start;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;

class StartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $resultMock;

    public function setUp()
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();

        $this->responseMock = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects(static::once())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));

        $this->contextMock->expects(static::once())
            ->method('getResponse')
            ->will($this->returnValue($this->responseMock));

        $this->contextMock->expects(static::once())
            ->method('getResultRedirectFactory')
            ->will($this->returnValue($this->resultRedirectFactoryMock));

        $this->contextMock->expects(static::once())
            ->method('getResultFactory')
            ->will($this->returnValue($this->resultFactoryMock));

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->getMock();
    }

    /**
     * @test
     */
    public function executeWithRedirectUrlTest()
    {
        $this->sessionMock->expects(static::once())
            ->method('getPmgwRedirectUrlValue')
            ->will($this->returnValue('http://example.com/redirect_url'));

        $this->resultFactoryMock->expects(static::once())
            ->method('create')
            ->with('redirect')
            ->will($this->returnValue($this->resultMock));

        $controller = new Start($this->contextMock, $this->sessionMock);

        $this->assertEquals($this->resultMock, $controller->execute());
    }

    /**
     * @test
     */
    public function executeWithoutRedirectUrlTest()
    {
        $this->sessionMock->expects(static::once())
            ->method('getPmgwRedirectUrlValue')
            ->will($this->returnValue(null));

        $this->resultFactoryMock->expects(static::never())
            ->method('create');

        $controller = new Start($this->contextMock, $this->sessionMock);

        $this->assertEquals(false, $controller->execute());
    }

}