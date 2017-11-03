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
namespace BigFish\Pmgw\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;

/**
 * Class Redirect
 *
 * @package BigFish\Pmgw\Controller\Payment
 */
class Redirect extends Action
{

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * @var string
     */
    protected $redirectUrl = '';

    /**
     * Redirect constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
    }

    /**
     * @return bool|\Magento\Framework\Controller\ResultInterface
     */
    public function execute() {

        $objectManager = ObjectManager::getInstance();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');

        $this->redirectUrl = $customerSession->getPmgwRedirectUrlValue();

        if ($this->redirectUrl) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->redirectUrl);
            return $resultRedirect;
        }

        return false;
    }

    /**
     * @param $url
     */
    public function setRedirectUrl($url) {
        $this->redirectUrl = $url;
    }
}