/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        var config = window.checkoutConfig.payment.bigfishpaymentgateway_pmgw.providers;

        if (config.length > 0) {
            for (var i=0; i<config.length; i++) {
                rendererList.push(
                    {
                        type: config[i].name,
                        component: 'Bigfishpaymentgateway_Pmgw/js/view/payment/method-renderer/bigfishpaymentgateway_pmgw'
                    }
                );
            }
        }

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
