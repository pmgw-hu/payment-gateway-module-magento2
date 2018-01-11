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
                template: 'BigFish_Pmgw/payment/form',
                redirectAfterPlaceOrder: false
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'provider': 'bigfish_pmgw'
                    }
                };
            },

            afterPlaceOrder: function () {
                window.location.replace(url.build('bigfish_pmgw/payment/start'));
            }

        });
    }
);