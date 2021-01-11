define([
        'jquery',
        'uiRegistry',
        'underscore'
    ],
    function ($, registry, _) {
        'use strict';

        return function (Component) {
            return Component.extend({
                initObservable: function () {
                    this._super();
                    if (typeof this.address() !== 'undefined' && this.address() !== null) {
                        var customAttributes = this.address().customAttributes;

                        // Remove non-visible customer address attributes
                        if (typeof registry.get('checkoutProvider').shippingAddress !== 'undefined' && typeof registry.get('checkoutProvider').shippingAddress.custom_attributes !== 'undefined') {
                            _(customAttributes).each(function (attribute, attributeCode) {
                                var isVisibleAttribute = registry.get('checkoutProvider').shippingAddress.custom_attributes['eacustomer_addressea_' + attributeCode];
                                if (typeof isVisibleAttribute === 'undefined') {
                                    this.address().customAttributes = _.reject(customAttributes, function (el) {
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
                        //console.log(this.address());
                    }

                    return this;
                }
            });
        }
    });