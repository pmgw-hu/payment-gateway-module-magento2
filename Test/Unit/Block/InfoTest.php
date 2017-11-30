<?php
namespace BigFish\Pmgw\Test\Unit\Block;

use BigFish\Pmgw\Test\Unit\Fixtures\Block\Info;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\Phrase;

class InfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    public function setUp()
    {
        parent::setUp();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMock();
    }

    /**
     * @test
     */
    public function getLabelTest()
    {
        $block = new Info(
            $this->contextMock,
            $this->configMock,
            []
        );

        $this->assertInstanceOf(Phrase::class, $block->getLabel('foo'));
        $this->assertEquals('foo', $block->getLabel('foo')->getText());
    }

}
