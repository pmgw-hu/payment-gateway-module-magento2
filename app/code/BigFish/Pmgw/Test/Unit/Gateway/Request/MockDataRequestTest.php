<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BigFish\Pmgw\Test\Unit\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use BigFish\Pmgw\Gateway\Http\Client\GatewayClient;
use BigFish\Pmgw\Gateway\Request\GatewayClientDataRequest;

class GatewayClientDataRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param int $forceResultCode
     * @param int|null $transactionResult
     *
     * @dataProvider transactionResultsDataProvider
     */
    public function testBuild($forceResultCode, $transactionResult)
    {
        $expectation = [
            GatewayClientDataRequest::FORCE_RESULT => $forceResultCode
        ];

        $paymentDO = $this->getMock(PaymentDataObjectInterface::class);
        $paymentModel = $this->getMock(InfoInterface::class);


        $paymentDO->expects(static::once())
            ->method('getPayment')
            ->willReturn($paymentModel);

        $paymentModel->expects(static::once())
            ->method('getAdditionalInformation')
            ->with('transaction_result')
            ->willReturn(
                $transactionResult
            );

        $request = new GatewayClientDataRequest();

        static::assertEquals(
            $expectation,
            $request->build(['payment' => $paymentDO])
        );
    }

    /**
     * @return array
     */
    public function transactionResultsDataProvider()
    {
        return [
            [
                'forceResultCode' => GatewayClient::SUCCESS,
                'transactionResult' => null
            ],
            [
                'forceResultCode' => GatewayClient::SUCCESS,
                'transactionResult' => GatewayClient::SUCCESS
            ],
            [
                'forceResultCode' => GatewayClient::FAILURE,
                'transactionResult' => GatewayClient::FAILURE
            ]
        ];
    }
}
