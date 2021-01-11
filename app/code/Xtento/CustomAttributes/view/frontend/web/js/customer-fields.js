define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        // 'model/shipping/customer-order-validator',
        // 'model/shipping/customer-order-rules'
    ],
    function () {
        'use strict';

        return {
            ajaxResponse: function (data) {
                return data
            }
        };


        function ajaxAddFields(url, data, elem, callback) {
            $.extend(data, {
                'form_key': $.mage.cookies.get('form_key')
            });

            $.ajax({
                url: url,
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,
                beforeSend: function () {
                    elem.attr('disabled', 'disabled');
                },
                complete: function () {
                    elem.attr('disabled', null);
                }
            })
                .done(function (response) {
                    if (response.success) {
                        callback.call(this, elem, response);
                    } else {
                        var msg = response.error_message;

                        if (msg) {
                            alert({
                                content: $.mage.__(msg)
                            });
                        }
                    }
                })
                .fail(function (error) {
                    console.log(JSON.stringify(error));
                });
        }
    }
);