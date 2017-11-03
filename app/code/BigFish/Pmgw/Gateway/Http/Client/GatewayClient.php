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
namespace BigFish\Pmgw\Gateway\Http\Client;

use BigFish\Pmgw\Model\Ui\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\App\ObjectManager;
use BigFish\PaymentGateway;
use BigFish\Pmgw\Gateway\Helper\Helper;
use Magento\Backend\Model\UrlInterface;
use BigFish\Pmgw\Model\PmgwAbstractFactory;
use BigFish\Pmgw\Model\LogAbstractFactory;

class GatewayClient implements ClientInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \BigFish\Pmgw\Model\Ui\ConfigProvider
     */
    private $config;

    /**
     * @var \BigFish\Pmgw\Model\PmgwAbstractFactory
     */
    private $pmgwFactory;

    /**
     * @var \BigFish\Pmgw\Model\LogAbstractFactory
     */
    private $logFactory;

    /**
     * GatewayClient constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \BigFish\Pmgw\Model\Ui\ConfigProvider $configProvider
     * @param \BigFish\Pmgw\Model\PmgwAbstractFactory $pmgwFactory
     * @param \BigFish\Pmgw\Model\LogAbstractFactory $logFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigProvider $configProvider,
        PmgwAbstractFactory $pmgwFactory,
        LogAbstractFactory $logFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $configProvider;
        $this->pmgwFactory = $pmgwFactory;
        $this->logFactory = $logFactory;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     *
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $response = $transferObject->getBody();

        if ($response[Helper::RESULT_CODE] === PaymentGateway::RESULT_CODE_SUCCESS) {

            $url = PaymentGateway::getStartUrl(new PaymentGateway\Request\Start($response[Helper::TXN_ID]));

            $objectManager = ObjectManager::getInstance();
            $customerSession = $objectManager->create('Magento\Customer\Model\Session');
            $customerSession->setPmgwRedirectUrlValue($url);
        }

        return $response;
    }

}
