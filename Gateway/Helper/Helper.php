<?php

namespace BigFish\Pmgw\Gateway\Helper;

use Magento\Braintree\Model\Paypal\Helper\AbstractHelper;

class Helper extends AbstractHelper {

    const TXN_ID = 'TransactionId';

    const RESULT_CODE = 'ResultCode';
    const RESULT_CODE_SUCCESSFUL = 'SUCCESSFUL';

    const RESULT_MESSAGE = 'ResultMessage';

    const TRANSACTION_STATUS_INITED = 100;
    const TRANSACTION_STATUS_STARTED = 110;
    const TRANSACTION_STATUS_SUCCESS = 120;
    const TRANSACTION_STATUS_CANCELLED = 130;
    const TRANSACTION_STATUS_FAILED = 200;

    const SUCCESS = 1;
    const FAILURE = 0;

}