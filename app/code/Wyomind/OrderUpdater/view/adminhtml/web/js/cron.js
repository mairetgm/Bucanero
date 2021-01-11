/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
define(["jquery"], function ($) {
    "use strict";
    return {
        loadSetting: function () {
            if ($('#cron_settings').val() === "") {
                $('#cron_settings').val('{"days":[],"hours":[]}');
            }
            var val = $.parseJSON($('#cron_settings').val());
            if (val !== null && typeof val.days) {
                $.each(val.days, function (index, elt) {
                    $('#d-' + elt).parent().addClass('selected');
                    $('#d-' + elt).prop('checked', true);
                });
                $.each(val.hours, function (index, elt) {
                    var hour = elt.replace(':', '');
                    $('#h-' + hour).parent().addClass('selected');
                    $('#h-' + hour).prop('checked', true);
                });
            }
        },
        /**
         * Update the json representation of the cron schedule
         */
        updateSetting: function () {
            var days = new Array();
            var hours = new Array();
            $('.cron-box.day').each(function () {
                if ($(this).prop('checked') === true) {
                    days.push($(this).attr('value'));
                }
            });
            $('.cron-box.hour').each(function () {
                if ($(this).prop('checked') === true) {
                    hours.push($(this).attr('value'));
                }
            });
            $('#cron_settings').val(JSON.stringify({days: days, hours: hours}));
        }
    }
});