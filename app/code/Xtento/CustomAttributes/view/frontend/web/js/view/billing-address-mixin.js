define([
        'jquery',
        'uiRegistry',
        'underscore'
    ],
    function ($, registry, _) {
        'use strict';

        return function (Component) {
            return Component.extend({
                updateAddress: function () {
                    if (typeof this.selectedAddress() !== 'undefined' && this.selectedAddress() !== null) {
                        var customAttributes = this.selectedAddress().customAttributes;

                        // Remove non-visible customer address attributes
                        if (typeof registry.get('checkoutProvider').shippingAddress !== 'undefined' && typeof registry.get('checkoutProvider').shippingAddress.custom_attributes !== 'undefined') {
                            _(customAttributes).each(function (attribute, attributeCode) {
                                var isVisibleAttribute = registry.get('checkoutProvider').shippingAddress.custom_attributes['eacustomer_addressea_' + attributeCode];
                                if (typeof isVisibleAttribute === 'undefined') {
                                    this.selectedAddress().customAttributes = _.reject(customAttributes, function (el) {
                                        return el.attribute_code === attributeCode;
                                    });
                                }
                            }.bind(this));
                        } else {
                            // Remove all custom attributes as there are none to display
                            _(customAttributes).each(function (attribute, attributeCode) {
                                this.address().customAttributes = _.reject(customAttributes, function (el) {
                                    return true
                                });
                            }.bind(this));
                        }
                    }

                    // Now call parent method
                    this._super();

                    return this;
                }
            });
        }
    });