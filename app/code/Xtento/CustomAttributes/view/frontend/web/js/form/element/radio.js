/**
 * @api
 */
define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'uiLayout'
], function (_, utils, registry, Abstract) {
    'use strict';
     return Abstract.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input',
            elementTmpl: 'Xtento_CustomAttributes/form/element/radio',
            caption: ''
        }
    });
});