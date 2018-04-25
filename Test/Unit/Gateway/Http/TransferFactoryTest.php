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
namespace Bigfishpaymentgateway\Pmgw\Test\Unit\Gateway\Http;

use Bigfishpaymentgateway\Pmgw\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;

class TransferFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $trasferBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $transferMock;

    public function setUp()
    {
        parent::setUp();

        $this->trasferBuilderMock = $this->getMockBuilder(TransferBuilder::class)
            ->disableArgumentCloning()
            ->getMock();

        $this->transferMock = $this->getMockBuilder(TransferInterface::class)
            ->getMock();
    }

    /**
     * @test
     */
    public function createTest()
    {
        $request = ['foo' => 'bar'];

        $factory = new TransferFactory($this->trasferBuilderMock);

        $this->trasferBuilderMock->expects(static::once())
            ->method('setBody')
            ->with($request)
            ->will($this->returnSelf());

        $this->trasferBuilderMock->expects(static::once())
            ->method('build')
            ->will($this->returnValue($this->transferMock));

        $this->assertEquals($this->transferMock, $factory->create($request));
    }

}
