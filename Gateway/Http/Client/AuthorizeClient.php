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

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Framework\App\ObjectManager;
use BigFish\PaymentGateway;
use BigFish\Pmgw\Gateway\Helper\Helper;

class AuthorizeClient implements ClientInterface
{
    /**
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $response = $transferObject->getBody();

        if ($response[Helper::RESPONSE_FIELD_RESULT_CODE] === PaymentGateway::RESULT_CODE_SUCCESS) {
            $url = PaymentGateway::getStartUrl(new PaymentGateway\Request\Start($response[Helper::RESPONSE_FIELD_TRANSACTION_ID]));

            ObjectManager::getInstance()->create('Magento\Customer\Model\Session')
                ->setPmgwRedirectUrlValue($url);
        }
        return $response;
    }

}
