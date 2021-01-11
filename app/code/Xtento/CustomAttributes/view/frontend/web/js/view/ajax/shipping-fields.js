define(
    [
        'jquery',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/url',
        'Magento_Checkout/js/model/error-processor'
    ],
    function ($, customer, quote, urlBuilder, urlFormatter, errorProcessor) {
        'use strict';

        return {

            /**
             * Make an ajax PUT request to store the order comment in the quote.
             *
             * @returns {Boolean}
             */
            setFields: function (apiFields) {
                var isCustomer = customer.isLoggedIn(),
                    quoteId = quote.getQuoteId(),
                    url,
                    payload,
                    result = true;

                if (isCustomer) {
                    url = urlBuilder.createUrl('/carts/mine/set-fields', {})
                } else {
                    url = urlBuilder.createUrl('/guest-carts/:cartId/set-fields', {cartId: quoteId});
                }

                payload = {
                    cartId: quoteId,
                    fields: {
                        fields: apiFields
                    }
                };

                $.ajax({
                    url: urlFormatter.build(url),
                    data: JSON.stringify(payload),
                    global: false,
                    contentType: 'application/json',
                    type: 'PUT',
                    async: false
                }).done(
                    function (response) {
                        result = true;
                    }
                ).fail(
                    function (response) {
                        result = false;
                        errorProcessor.process(response);
                    }
                );

                return result;
            },
            uploadFiles: function (uploadFields, apiFields) {
                var formData = new FormData(),
                    ajaxUrl = urlFormatter.build('xtento_customattributes/index/upload');

                var uploadedCount = 0;
                $.each(uploadFields, function (name, element) {
                    if (typeof element[0].files[0] === 'undefined') return;
                    var originalAttributeCode = name.replace('custom_attributes[', '').replace(']', '').replace('eaorder_fieldea_', '').replace('eacustomer_addressea_', '').replace('eacustomerea_', '');
                    var newFilename = apiFields[originalAttributeCode];
                    if (newFilename === '') {
                        newFilename = element[0].files[0].name;
                    }
                    formData.append(name, element[0].files[0], newFilename);
                    uploadedCount++;
                });

                if (uploadedCount === 0) {
                    return;
                }

                $.ajax({
                    processData: false,
                    contentType: false,
                    showLoader: true,
                    url: ajaxUrl,
                    data: formData,
                    type: "POST",
                    success: function (result) {
                        // console.log(result)
                    }
                });
            }
        };
    }
);