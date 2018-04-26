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
namespace Bigfishpaymentgateway\Pmgw\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Customer\Model\Session;

class Start extends Action
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(Context $context, Session $customerSession)
    {
        parent::__construct($context);

        $this->customerSession = $customerSession;
    }

    /**
     * @return bool|ResultInterface
     */
    public function execute()
    {
        $redirectUrl = $this->customerSession->getPmgwRedirectUrlValue();

        if ($redirectUrl) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($redirectUrl);
            return $resultRedirect;
        }
        return false;
    }

}
