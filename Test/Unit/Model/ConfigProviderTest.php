<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
 * @category   BigFish
 * @package    BigFish_Pmgw
 * @author     BIG FISH Ltd., paymentgateway [at] bigfish [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */
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
     * @test
     */
    public function getKhbCardPocketIdTest()
    {
        $configProvider = new ConfigProvider($this->scopeConfigMock);

        $result = $configProvider->getKhbCardPocketId();

        $this->assertEquals(4, count($result));

        $this->assertEquals([
            '', '1', '2', '3'
        ], array_keys($result));
    }

    /**
     * @test
     */
    public function getMkbCardPocketIdTest()
    {
        $configProvider = new ConfigProvider($this->scopeConfigMock);

        $result = $configProvider->getMkbCardPocketId();

        $this->assertEquals(4, count($result));

        $this->assertEquals([
            '', '1111', '2222', '3333'
        ], array_keys($result));
    }

    /**
     * @test
     */
    public function getOtpCardPocketIdTest()
    {
        $configProvider = new ConfigProvider($this->scopeConfigMock);

        $result = $configProvider->getOtpCardPocketId();

        $this->assertEquals(4, count($result));

        $this->assertEquals([
            '', '09', '07', '08'
        ], array_keys($result));
    }

    /**
     * @test
     */
    public function getSaferpayPaymentMethodsTest()
    {
        $configProvider = new ConfigProvider($this->scopeConfigMock);

        $result = $configProvider->getSaferpayPaymentMethods();

        $this->assertEquals(20, count($result));
    }

    /**
     * @test
     */
    public function getSaferpayWalletsTest()
    {
        $configProvider = new ConfigProvider($this->scopeConfigMock);

        $result = $configProvider->getSaferpayWallets();

        $this->assertEquals(1, count($result));
    }

    /**
     * @test
     */
    public function getWirecardPaymentTypesTest()
    {
        $configProvider = new ConfigProvider($this->scopeConfigMock);

        $result = $configProvider->getWirecardPaymentTypes();

        $this->assertEquals(25, count($result));
    }

    /**
     * @param array $scopeConfig
     * @param array $expected
     */
    private function assertConfig(array $scopeConfig, array $expected)
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
    private function setScopeConfigMockGetValue(array $scopeConfig)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with('payment')
            ->will($this->returnValue($scopeConfig));
    }

}
