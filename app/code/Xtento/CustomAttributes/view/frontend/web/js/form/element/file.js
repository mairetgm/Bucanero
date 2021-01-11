/**
* @api
*/
define([
   'Magento_Ui/js/form/element/textarea'
], function (Abstract) {
   'use strict';

    return Abstract.extend({
       defaults: {
           cols: 15,
           rows: 2,
           elementTmpl: 'Xtento_CustomAttributes/form/element/file'
       }
   });
});