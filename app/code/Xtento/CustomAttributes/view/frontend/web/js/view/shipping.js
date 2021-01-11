define(['jquery', 'mage/translate'],
    function ($, $t) {
        'use strict';

        return function (Component) {
            return Component.extend({
                validateShippingInformation: function () {
                    var formErrors = '#shipping-checkout-form div.field-error',
                        notValid = [];

                    $(formErrors).each(function () {
                        notValid.push(true)
                    });

                    this.source.set('params.invalid', false);

                    if (notValid.length > 0) {
                        this.source.set('params.invalid', true)
                        return false;
                    }

                    /*this.errorValidationMessage(
                        $t('Required fields have not been populated. Please enter values for required fields.')
                    );
                    return false;*/

                    var result = this._super();
                    return result;
                }
            });
        }
    });