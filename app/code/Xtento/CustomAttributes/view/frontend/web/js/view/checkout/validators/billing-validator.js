define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Xtento_CustomAttributes/js/model/checkout/validators/billing-validator'
    ],
    function (Component, additionalValidators, billingValidator) {
        'use strict';
        additionalValidators.registerValidator(billingValidator);
        return Component.extend({});
    }
);
