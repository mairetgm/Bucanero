/**
 * @api
 */
define([
    'moment',
    'mageUtils',
    'Magento_Ui/js/form/element/date',
    'moment-timezone-with-data'
], function (moment, utils, Date) {
    'use strict';

    return Date.extend({
        defaults: {
            elementTmpl: 'Xtento_CustomAttributes/form/element/date'
        },
        /**
         * Initializes regular properties of instance.
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();

            this.validationParams = '';

            if (!this.options.dateFormat) {
                this.options.dateFormat = this.pickerDefaultDateFormat;
            }

            if (!this.options.timeFormat) {
                this.options.timeFormat = this.pickerDefaultTimeFormat;
            }

            this.prepareDateTimeFormats();

            return this;
        },

        /**
         * Prepares and converts all date/time formats to be compatible
         * with moment.js library.
         */
        prepareDateTimeFormats: function () {
            this.pickerDateTimeFormat = this.options.dateFormat;

            if (this.options.showsTime) {
                this.pickerDateTimeFormat += ' ' + this.options.timeFormat;
            }

            this.pickerDateTimeFormat = utils.convertToMomentFormat(this.pickerDateTimeFormat);

            if (this.options.dateFormat) {
                this.outputDateFormat = this.options.dateFormat;
            }

            this.inputDateFormat = utils.convertToMomentFormat(this.inputDateFormat);
            this.outputDateFormat = utils.convertToMomentFormat(this.outputDateFormat);

            // this.validationParams.dateFormat = this.outputDateFormat;
        }
    });
});
