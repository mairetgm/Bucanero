define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
            
        rendererList.push(
            {
                type: 'rootways_psigate_option',
                component: 'Rootways_Psigate/js/view/payment/method-renderer/psigate-method'
            }
        );
            
        /** Add view logic here if needed */
        return Component.extend({});
    }
);