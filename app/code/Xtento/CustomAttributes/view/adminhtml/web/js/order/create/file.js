define([
    'jquery'
], function (jQuery) {
    'use strict';

    var $formEl = jQuery('#edit_form'), fileFieldsBilling, fileFieldsShipping;

    if (!$formEl.length || !$formEl.data('order-config')) {
        return;
    }

    fileFieldsBilling = $formEl.find("input[type='file'][name*='order[billing_address]']");
    fileFieldsShipping = $formEl.find("input[type='file'][name*='order[shipping_address]']");

    fileFieldsBilling.each(function () {
        jQuery(this).val('');
        $formEl.find("input[name = '" + jQuery(this).attr('name') + "[value]']").val('');
        jQuery(this).on('change', function () {
            var el = jQuery(this),
                files = el[0].files,
                name = el.attr('name'),
                hiddenInput = $formEl.find("input[name = '" + name + "[value]']");

            if (files) {
                hiddenInput.val(files[0].name);
            }
        });

    });

    fileFieldsShipping.each(function () {
        jQuery(this).val('');
        $formEl.find("input[name = '" + jQuery(this).attr('name') + "[value]']").val('');
        jQuery(this).on('change', function () {
            var el = jQuery(this),
                files = el[0].files,
                name = el.attr('name'),
                hiddenInput = $formEl.find("input[name = '" + name + "[value]']");

            if (files) {
                hiddenInput.val(files[0].name);
            }
        });

    });
});