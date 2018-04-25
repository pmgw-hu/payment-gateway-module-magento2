/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (
        Component,
        url
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Bigfishpaymentgateway_Pmgw/payment/form',
                redirectAfterPlaceOrder: false
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'provider': 'bigfishpaymentgateway_pmgw'
                    }
                };
            },

            afterPlaceOrder: function () {
                window.location.replace(url.build('bigfishpaymentgateway_pmgw/payment/start'));
            }

        });
    }
);