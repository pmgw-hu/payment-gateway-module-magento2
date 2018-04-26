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
namespace Bigfishpaymentgateway\Pmgw\Test\Unit\Controller\Payment;

use Bigfishpaymentgateway\Pmgw\Test\Unit\Fixtures\SessionFixture as Session;
use Bigfishpaymentgateway\Pmgw\Test\Unit\Fixtures\Model\Response\ResultInterface;
use Bigfishpaymentgateway\Pmgw\Controller\Payment\Start as StartController;

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