var config = {
    map: {
        '*': {
            'Magento_Checkout/js/action/select-shipping-address':
                'Xtento_CustomAttributes/js/action/select-shipping-address-mixin',
            'Magento_Checkout/template/shipping-information/address-renderer/default.html':
                'Xtento_CustomAttributes/template/shipping-information/address-renderer/default.html',
            'Magento_Checkout/template/billing-address/details.html':
                'Xtento_CustomAttributes/template/billing-address/details.html',
            'Magento_Checkout/template/shipping-address/address-renderer/default.html':
                'Xtento_CustomAttributes/template/shipping-address/address-renderer/default.html',
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-billing-address': {
                'Xtento_CustomAttributes/js/action/set-billing-information-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Xtento_CustomAttributes/js/action/set-billing-information-mixin': true
            },
            'Magento_Checkout/js/action/create-billing-address': {
                'Xtento_CustomAttributes/js/action/set-billing-information-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Xtento_CustomAttributes/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/create-shipping-address': {
                'Xtento_CustomAttributes/js/action/create-shipping-address-mixin': true
            },
            "Magento_Checkout/js/view/shipping": {
                "Xtento_CustomAttributes/js/view/shipping": true
            },
            "Magento_Checkout/js/view/billing-address": {
                "Xtento_CustomAttributes/js/view/billing-address-mixin": true
            },
            "Magento_Checkout/js/view/shipping-address/address-renderer/default": {
                "Xtento_CustomAttributes/js/view/shipping-address/address-renderer/default-mixin": true
            }
        }
    }
};