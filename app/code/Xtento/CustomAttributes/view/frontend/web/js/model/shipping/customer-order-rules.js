define(
    [],
    function () {
        'use strict';
        return {
            getRules: function () {
                return {
                    'postcode': {
                        'required': true
                    },
                    'xteaordereaxt_customer_f2': {
                        'required': true
                    }
                };
            }
        };
    }
);
