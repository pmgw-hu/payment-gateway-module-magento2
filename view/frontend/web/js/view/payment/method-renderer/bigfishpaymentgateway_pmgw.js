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
                        'provider': 'bigfishpaymentgateway_pmgw',
                        'card_registration': this.isPaymentMethodAccept(this.item.method),
                        'bankowner': 'owner'
                    }
                };
            },

            afterPlaceOrder: function () {
                window.location.replace(url.build('bigfishpaymentgateway_pmgw/payment/start'));
            },

	        getInstructions: function () {
		        return window.checkoutConfig.payment.instructions[this.item.method];
	        },

	        getCardRegistrationCode : function () {
		        var config = window.checkoutConfig.payment.bigfishpaymentgateway_pmgw.providers;
		        if (config.length > 0) {
			        for (var i=0; i<config.length; i++) {
				        if (config[i].name === this.item.method) {
					        return config[i].card_registration_mode;
				        }
			        }
		        }
	        },

	        getDescription : function () {
		        var config = window.checkoutConfig.payment.bigfishpaymentgateway_pmgw.providers;
		        if (config.length > 0) {
			        for (var i=0; i<config.length; i++) {
				        if (config[i].name === this.item.method && config[i].description) {
					        return config[i].description;
				        }
			        }
			        return null;
		        }
	        },

	        cardDescriptionEnabled: function () {
		        if (this.getDescription() != null) {
			        return true;
		        }

		        return false;
	        },

	        cardRegistrationEnabled: function () {
            	var code = this.getCardRegistrationCode();
		        if (code == 1 || code == 2) {
			        return true;
		        }

		        return false;
	        },

	        isPaymentMethodAccept: function (selectedProvider) {
		        var checkboxes = document.getElementsByName('payment[method][card_registration]');

		        if (checkboxes.length > 0) {
			        for (var i=0; i<checkboxes.length; i++) {
				        if (checkboxes[i].value === selectedProvider && checkboxes[i].checked === true) {
                            return true;
                        }
			        }
		        }

		        return false;
	        }
        });
    }
);