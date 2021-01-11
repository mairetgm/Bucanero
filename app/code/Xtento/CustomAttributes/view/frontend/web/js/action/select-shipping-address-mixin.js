

define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    return function (shippingAddress) {
        if (shippingAddress !== null && typeof shippingAddress !== 'undefined' && shippingAddress.customAttributes !== undefined) {
            $.each(shippingAddress.customAttributes , function( key, value ) {
                if($.isArray(value)) {
                    value = value.toString();
                }
                shippingAddress['customAttributes'][key] = value;
            })
        }

        quote.shippingAddress(shippingAddress);
    };
});
