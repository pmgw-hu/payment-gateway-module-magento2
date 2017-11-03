<?php
/**
 * BIG FISH Ltd.
 * http://www.bigfish.hu
 *
 * @title      Magento -> Custom Payment Module for BIG FISH Payment Gateway
 * @category   BigFish
 * @package    BigFish_Pmgw
 * @author     Polyak Sandor / BIG FISH Ltd. -> sandor.polyak [at] bigfish
 *     [dot] hu
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software
 *     License (OSL 3.0)
 * @copyright  Copyright (c) 2017, BIG FISH Ltd.
 */

namespace BigFish\Pmgw\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

class TransferFactory implements TransferFactoryInterface {

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * TransferFactory constructor.
     *
     * @param \Magento\Payment\Gateway\Http\TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $body
     *
     * @return \Magento\Payment\Gateway\Http\TransferInterface
     */
    public function create(array $body) {

        return $this->transferBuilder
            ->setBody($body)
            ->setMethod('POST')
            ->build();
    }
}