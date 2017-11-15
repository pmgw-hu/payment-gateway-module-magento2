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
namespace BigFish\Pmgw\Model\Provider;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class Provider
 *
 * @package BigFish\Pmgw\Model\Provider
 */
class Provider extends AbstractMethod
{
    /**
     * @var string
     */
    protected $_code;

    /**
     * @var
     */
    protected $_paymentMethod;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @return mixed
     */
    public function getCode() {
        return $this->_code;
    }

    /**
     * @return mixed
     */
    public function getProviderCode() {
        return $this->_paymentMethod;
    }

    /**
     * @return bool
     */
    public function isInitializeNeeded() {
        return true;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        $payment = $this->getInfoInstance();
    }

}