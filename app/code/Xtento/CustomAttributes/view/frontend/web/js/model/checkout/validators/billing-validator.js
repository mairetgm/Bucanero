define(
    [
        'jquery',
        'uiRegistry',
        'mage/validation',
        '../../../view/ajax/billing-fields',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, registry, Validation, billingFields, quote) {
        'use strict';

        return {
            validate: function () {
                var source = registry.get('checkoutProvider'),
                    formErrors = '#custom-checkout-form div.field-error',
                    notValid = [],
                    apiFields = {},
                    fileFields = {},
                    result,
                    beforeOrderCustomAttributes = source.get('before-place-order'),
                    customAttributes = source.get('custom_attributes'),
                    billingAddress = quote.billingAddress();

                source.trigger('customCheckoutForm.data.validate');

                $(formErrors).each(function () {
                    notValid.push(true)
                });

                var bcCustomAttributes = {};
                if (beforeOrderCustomAttributes !== undefined && beforeOrderCustomAttributes.custom_attributes !== undefined) {
                    bcCustomAttributes = beforeOrderCustomAttributes.custom_attributes;
                }
                var billingAddressAttributes = {};
                if (billingAddress !== undefined && billingAddress !== null && billingAddress.custom_attributes !== undefined) {
                    billingAddressAttributes = billingAddress.custom_attributes;
                }
                var attributes = $.extend(
                    {},
                    bcCustomAttributes,
                    customAttributes,
                    billingAddressAttributes
                );

                $.each(attributes, function (key, value) {
                    if (key.includes('eaorder_fieldea')) {
                        if ($.isArray(value)) {
                            value = value.toString();
                        }

                        // Files
                        var element = $('.checkout-payment-method input[name*="' + key + '"]'),
                            files,
                            fileName;
                        if (element.length) {
                            files = element[0].files;
                            if (files !== null && files.length) {
                                fileName = files[0].name;
                                value = (new Date().getTime()).toString(16) + '_' + fileName.replace(/[^\x00-\x7F]/g, "");
                            }
                        }

                        // Save field
                        apiFields[key.replace('eaorder_fieldea_', '')] = value;
                    }
                });

                result = false;

                $.each($(document).find('input[type=file]'), function () {
                    var name = $(this).attr('name'),
                        el = $(this);
                    fileFields[name] = el;
                });

                if (!$.isEmptyObject(fileFields)) {
                    billingFields.uploadFiles(fileFields, apiFields);
                }

                if (notValid.length === 0) {
                    billingFields.setFields(apiFields);
                    result = true;
                }

                return result;
            }
        };
    }
);