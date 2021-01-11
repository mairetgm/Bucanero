define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    '../view/ajax/shipping-fields',
], function ($, wrapper, quote, shippingFields) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {

            var shippingAddress = quote.shippingAddress(),
                billingAddress = quote.billingAddress(),
                apiFields = {},
                fileFields = {},
                address_values = {},
                customer_values = {}, customFieldSets;

            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            if (billingAddress !== null && billingAddress !== undefined) {
                if (billingAddress.customAttributes !== undefined) {
                    billingAddress.customAttributes = {};
                }
            }

            if (shippingAddress !== null && typeof shippingAddress !== 'undefined' && shippingAddress.customAttributes !== undefined) {
                $.each(shippingAddress.customAttributes, function (key, value) {
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
                        var element = $('input[name*="' + uploadName + '"]'),
                            files,
                            fileName;

                        if (element.length) {
                            files = element[0].files;
                            if (files !== null && files.length) {
                                fileName = files[0].name;
                                shippingAddress.customAttributes[key].value = fileName;
                            }
                        }
                    }

                    keyVal = attributeCode;

                    if ($.isPlainObject(value)) {
                        value = value['value'];
                    }

                    if (value[0] === '') {
                        value = '';
                    }

                    if ($.isArray(value)) {
                        value = value.join(',');
                    }

                    if (keyVal.indexOf('eacustomer_addressea') !== -1) {
                        address_values[keyVal.replace('eacustomer_addressea_', '')] = value;
                    }

                    if (keyVal.indexOf('eacustomerea') !== -1) {
                        customer_values[keyVal.replace('eacustomerea_', '')] = value;
                    }

                    if (keyVal.indexOf('eaorder_fieldea') !== -1) {
                        apiFields[keyVal.replace('eaorder_fieldea_', '')] = value;
                    }
                });

                if (shippingAddress['extension_attributes']['customer_address'] == undefined) {
                    shippingAddress['extension_attributes']['customer_address'] = {};
                }

                if (shippingAddress['extension_attributes']['customer'] == undefined) {
                    shippingAddress['extension_attributes']['customer'] = {};
                }

                shippingAddress['extension_attributes']['customer_address']['value'] = address_values;
                shippingAddress['extension_attributes']['customer']['value'] = customer_values;
            }

            customFieldSets = $("input[name*='eacustomer']");

            if (customFieldSets.length) {
                $.each(customFieldSets.find(':input'), function () {
                    var el = $(this),
                        name = $(this).attr('name'),
                        value = $(this).val(),
                        fieldName = name.split("[")[1].split("]")[0],
                        files, fileName;

                    apiFields[fieldName.replace('eaorder_fieldea_', '')] = value;

                    files = el[0].files;
                    if (files !== null && files.length) {
                        fileName = files[0].name;
                        apiFields[fieldName.replace('eaorder_fieldea_', '')] = (new Date().getTime()).toString(16) + '_' + fileName.replace(/[^\x00-\x7F]/g, "");
                    }
                });
            }

            $.each($(document).find('input[type=file]'), function () {
                var name = $(this).attr('name'),
                    el = $(this);
                fileFields[name] = el;
            });

            if (!$.isEmptyObject(fileFields)) {
                shippingFields.uploadFiles(fileFields, apiFields);
            }

            if (!$.isEmptyObject(apiFields)) {
                shippingFields.setFields(apiFields);
            }

            return originalAction(messageContainer);
        });
    };
});