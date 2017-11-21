/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/action/place-order',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url'
    ],
    function ($,
              ko,
              Component,
              setPaymentInformationAction,
              placeOrderAction,
              customer,
              checkoutData,
              additionalValidators,
              fullScreenLoader,
              url
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'BigFish_Pmgw/payment/form',
                transactionResult: '',
                providers: window.checkoutConfig.payment.bigfish_pmgw.providers
            },

            placeOrderHandler: null,

            /**
             * @returns {exports.initialize}
             */
            initialize: function () {
                this._super();

                return this;
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'methods'
                    ]);
                return this;
            },

            /**
             * @param {Function} handler
             */
            setPlaceOrderHandler: function(handler) {
                this.placeOrderHandler = handler;
            },

            getCode: function() {
                return this.item.method;
            },

            getTitle: function() {
                return this.item.title;
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'provider': 'bigfish_pmgw',
                        'providers': this.methods()
                    }
                };
            },

            getTransactionResults: function() {
                return _.map(window.checkoutConfig.payment.bigfish_pmgw.transactionResults, function(value, key) {
                    return {
                        'value': key
                    }
                });
            },

            /**
             * Trigger order placing
             */
            placeOrder: function (data, event) {
                this.selectPaymentMethod(); // save selected payment method in Quote

                if (event) {
                    event.preventDefault();
                }
                var self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    this.beforePlaceOrder();
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);
                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }
                return false;
            },

            beforePlaceOrder: function () {

            },

            afterPlaceOrder: function () {
                window.location.replace(url.build('bigfish_pmgw/payment/start'));
            }

        });
    }
);