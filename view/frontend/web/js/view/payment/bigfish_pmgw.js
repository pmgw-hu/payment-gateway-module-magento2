/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        var config = window.checkoutConfig.payment.bigfish_pmgw.providers;

        if (config.length > 0) {
            for (var i=0; i<config.length; i++) {
                rendererList.push(
                    {
                        type: config[i].name,
                        component: 'BigFish_Pmgw/js/view/payment/method-renderer/bigfish_pmgw'
                    }
                );
            }
        }

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
