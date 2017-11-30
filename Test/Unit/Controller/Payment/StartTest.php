<?php
namespace BigFish\Pmgw\Test\Unit\Controller\Payment;

use BigFish\Pmgw\Test\Unit\Fixtures\SessionFixture as Session;
use BigFish\Pmgw\Test\Unit\Fixtures\Model\Response\ResultInterface;
use BigFish\Pmgw\Controller\Payment\Start as StartController;

class StartTest extends AbstractTest
{
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
        parent::setUp();

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

        $controller = new StartController($this->contextMock, $this->sessionMock);

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

        $controller = new StartController($this->contextMock, $this->sessionMock);

        $this->assertEquals(false, $controller->execute());
    }

}