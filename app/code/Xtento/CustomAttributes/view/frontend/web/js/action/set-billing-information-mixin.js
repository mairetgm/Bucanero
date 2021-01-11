define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
], function ($, wrapper, quote) {
    'use strict';

    return function (setBillingAddressAction) {
        return wrapper.wrap(setBillingAddressAction, function (originalAction, messageContainer) {

            var billingAddress = quote.billingAddress(),
                address_values = {}, customer_values = {};

            if (billingAddress != undefined && billingAddress !== null) {

                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }

                if (billingAddress.customAttributes != undefined) {
                    $.each(billingAddress.customAttributes, function (key, value) {
                        var attributeCode = '';
                        if ($.isArray(value)) {
                            attributeCode = value['attribute_code'];
                        } else if (typeof value === 'object' && value !== null) {
                            attributeCode = value.attribute_code;
                        } else {
                            attributeCode = key;
                        }

                        var uploadName = attributeCode, keyVal;

                        if (uploadName !== undefined) {
                            var element = $('.payment-methods input[name*="' + uploadName + '"]'),
                                files,
                                fileName;

                            if (element.length) {
                                files = element[0].files;
                                if (files !== null && files.length) {
                                    fileName = files[0].name;
                                    //console.log(fileName);
                                    billingAddress.customAttributes[key].value = fileName;
                                }
                            }
                        }

                        if (value[0] === '') {
                            value = '';
                        }

                        keyVal = attributeCode;

                        if ($.isPlainObject(value)) {
                            value = value['value'];
                        }

                        if ($.isArray(value)) {
                            value = value.toString();
                        }

                        if (keyVal.indexOf('eacustomer_addressea') !== -1) {
                            address_values[keyVal.replace('eacustomer_addressea_', '')] = value;
                        }

                        if (keyVal.indexOf('eacustomerea') !== -1) {
                            customer_values[keyVal.replace('eacustomerea_', '')] = value;
                        }

                    });

                    if (billingAddress['extension_attributes']['customer_address'] == undefined) {
                        billingAddress['extension_attributes']['customer_address'] = {};
                    }

                    if (billingAddress['extension_attributes']['customer'] == undefined) {
                        billingAddress['extension_attributes']['customer'] = {};
                    }

                    billingAddress['extension_attributes']['customer_address']['value'] = address_values;
                    billingAddress['extension_attributes']['customer']['value'] = customer_values;
                }
            }

            return originalAction(messageContainer);
        });
    };
});