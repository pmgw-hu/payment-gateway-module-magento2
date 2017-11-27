<?php
namespace BigFish\Pmgw\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use BigFish\Pmgw\Model\ConfigProvider;

class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    public function setUp()
    {
        parent::setUp();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function emptyScopeConfigTest()
    {
        $this->assertConfig([], []);
    }

    /**
     * @test
     */
    public function noPmgwProviderInScopeConfigTest()
    {
        $this->assertConfig([
            'bigfish_pmgw' => [
                'debug' => 1,
            ],
            'other_provider' => [
                'foo' => 'bar',
            ],
        ], []);
    }

    /**
     * @test
     */
    public function noActivePmgwProviderInScopeConfigTest()
    {
        $this->assertConfig([
            'bigfish_pmgw' => [
                'debug' => 1,
            ],
            'bigfish_pmgw_test' => [
                'active' => 0,
            ],
        ], []);
    }

    /**
     * @test
     */
    public function oneActivePmgwProviderInScopeConfigTest()
    {
        $this->assertConfig([
            'bigfish_pmgw' => [
                'debug' => 1,
            ],
            'bigfish_pmgw_test' => [
                'active' => 1,
            ],
        ], [
            [
                'name' => 'bigfish_pmgw_test',
                'active' => 1,
                'debug' => 1,
            ],
        ]);
    }

    /**
     * @test
     */
    public function multipleActivePmgwProviderInScopeConfigTest()
    {
        $this->assertConfig([
            'bigfish_pmgw' => [
                'debug' => 1,
            ],
            'bigfish_pmgw_foo' => [
                'active' => 1,
            ],
            'bigfish_pmgw_bar' => [
                'active' => 1,
            ],
        ], [
            [
                'name' => 'bigfish_pmgw_foo',
                'active' => 1,
                'debug' => 1,
            ],
            [
                'name' => 'bigfish_pmgw_bar',
                'active' => 1,
                'debug' => 1,
            ],
        ]);
    }

    /**
     * @test
     */
    public function multipleActiveAndInactivePmgwProviderInScopeConfigTest()
    {
        $this->assertConfig([
            'bigfish_pmgw' => [
                'debug' => 1,
            ],
            'bigfish_pmgw_foo' => [
                'active' => 1,
            ],
            'bigfish_pmgw_test' => [
                'active' => 0,
            ],
            'bigfish_pmgw_bar' => [
                'active' => 1,
            ],
        ], [
            [
                'name' => 'bigfish_pmgw_foo',
                'active' => 1,
                'debug' => 1,
            ],
            [
                'name' => 'bigfish_pmgw_bar',
                'active' => 1,
                'debug' => 1,
            ],
        ]);
    }

    /**
     * @test
     */
    public function unifyPmgwProviderConfigValuesTest()
    {
        $this->assertConfig([
            'bigfish_pmgw' => [
                'debug' => 1,
            ],
            'bigfish_pmgw_foo' => [
                'active' => 1,
                'foo' => 'bar',
            ],
            'bigfish_pmgw_bar' => [
                'active' => 1,
                'bar' => 'foo',
            ],
        ], [
            [
                'name' => 'bigfish_pmgw_foo',
                'active' => 1,
                'foo' => 'bar',
                'bar' => null,
                'debug' => 1,
            ],
            [
                'name' => 'bigfish_pmgw_bar',
                'active' => 1,
                'foo' => null,
                'bar' => 'foo',
                'debug' => 1,
            ],
        ]);
    }

    /**
     * @test
     */
    public function getProviderConfigWithInvalidCodeTest()
    {
        $this->setScopeConfigMockGetValue([
            'bigfish_pmgw' => [
                'debug' => 1,
            ],
            'bigfish_pmgw_test' => [
                'active' => 1,
            ],
        ]);

        $configProvider = new ConfigProvider($this->scopeConfigMock);

        $this->assertEquals([], $configProvider->getProviderConfig('bigfish_pmgw_foo'));
    }

    /**
     * @test
     */
    public function getProviderConfigWithValidCodeTest()
    {
        $this->setScopeConfigMockGetValue([
            'bigfish_pmgw' => [
                'debug' => 1,
            ],
            'bigfish_pmgw_test' => [
                'active' => 1,
            ],
        ]);

        $configProvider = new ConfigProvider($this->scopeConfigMock);

        $this->assertEquals([
            'name' => 'bigfish_pmgw_test',
            'active' => 1,
            'debug' => 1,
        ], $configProvider->getProviderConfig('bigfish_pmgw_test'));
    }

    /**
     * @param array $scopeConfig
     * @param array $expected
     */
    protected function assertConfig(array $scopeConfig, array $expected)
    {
        $this->setScopeConfigMockGetValue($scopeConfig);

        $configProvider = new ConfigProvider($this->scopeConfigMock);

        $this->assertEquals([
            'payment' => [
                'bigfish_pmgw' => [
                    'providers' => $expected,
                ],
            ],
        ], $configProvider->getConfig());
    }

    /**
     * @param array $scopeConfig
     */
    protected function setScopeConfigMockGetValue(array $scopeConfig)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with('payment')
            ->will($this->returnValue($scopeConfig));
    }

}
