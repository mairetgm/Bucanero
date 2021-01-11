
/**
 * @api
 */
define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/element/multiselect'
], function (_, utils, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            size: 5,
            elementTmpl: 'Xtento_CustomAttributes/form/element/multiselect',
            listens: {
                value: 'setDifferedFromDefault setPrepareToSendData'
            }
        }
    });
});
