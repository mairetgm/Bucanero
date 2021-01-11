<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-12-03T09:29:15+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Sources/ShowOnAddress.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Sources;

use Xtento\CustomAttributes\Helper\Data;

class ShowOnAddress extends AbstractSource
{
    const SHIPPING_ADDRESS_BEFORE_FORM = [
        Data::LOCATION_ID => 3,
        Data::STEP => 'shippingAddress',
        Data::LOCATION => 'before-form',
    ];

    const SHIPPING_ADDRESS_BEFORE_SHIPPING_METHOD = [
        Data::LOCATION_ID => 4,
        Data::STEP => 'shippingAddress',
        Data::LOCATION => 'before-shipping-method-form',
    ];

    const BILLING_ADDRESS_BEFORE_PLACE_ORDER = [
        Data::LOCATION_ID => 5,
        Data::STEP => 'billingAddress',
        Data::LOCATION => 'before-place-order',
        Data::PARENT_LOCATION => 'payments-list'
    ];

    const BILLING_ADDRESS_PAYMENT_AFTER_METHODS = [
        Data::LOCATION_ID => 6,
        Data::STEP => 'billingAddress',
        Data::LOCATION => 'afterMethods',
        Data::PARENT_LOCATION => 'afterMethods'
    ];

    const BILLING_ADDRESS_PAYMENT_BEFORE_METHODS = [
        Data::LOCATION_ID => 7,
        Data::STEP => 'billingAddress',
        Data::LOCATION => 'beforeMethods',
        Data::PARENT_LOCATION => 'beforeMethods'
    ];

    /**
     * @return array
     */
    public function getAvailable()
    {
        return [
            '' => __('--- Select ---'),
            Data::AVAILABLE_HIDDEN => __('Nowhere / Hidden'),
            Data::AVAILABLE_ON_BILLING => __('Billing Address'),
            Data::AVAILABLE_ON_SHIPPING => __('Shipping Address'),
            Data::AVAILABLE_ON_BOTH => __('Both Addresses'),
            self::SHIPPING_ADDRESS_BEFORE_FORM[Data::LOCATION_ID] => __('Above shipping address'),
            self::SHIPPING_ADDRESS_BEFORE_SHIPPING_METHOD[Data::LOCATION_ID] => __('Above shipping methods'),
            self::BILLING_ADDRESS_PAYMENT_BEFORE_METHODS[Data::LOCATION_ID] => __('Above payment methods'),
            self::BILLING_ADDRESS_PAYMENT_AFTER_METHODS[Data::LOCATION_ID] => __('Below payment methods'),
            self::BILLING_ADDRESS_BEFORE_PLACE_ORDER[Data::LOCATION_ID] => __('Above place order')
        ];
    }

    /**
     * @return array
     */
    public function getAvailableNotOrder()
    {
        return [
            '' => __('--- Select ---'),
            Data::AVAILABLE_HIDDEN => __('Nowhere / Hidden'),
            Data::AVAILABLE_ON_BILLING => __('Billing Address'),
            Data::AVAILABLE_ON_SHIPPING => __('Shipping Address'),
            Data::AVAILABLE_ON_BOTH => __('Both Addresses')
        ];
    }

    public function toOptionArray()
    {
        $getAvailable = $this->getAvailable();

        $options = [];
        foreach ($getAvailable as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toArrayLocations()
    {
        return [
            self::SHIPPING_ADDRESS_BEFORE_FORM[Data::LOCATION_ID] =>
                [
                    Data::LOCATION => self::SHIPPING_ADDRESS_BEFORE_FORM[Data::LOCATION],
                    Data::PARENT_LOCATION => ''
                ],
            self::SHIPPING_ADDRESS_BEFORE_SHIPPING_METHOD[Data::LOCATION_ID] =>
                [
                    Data::LOCATION => self::SHIPPING_ADDRESS_BEFORE_SHIPPING_METHOD[Data::LOCATION],
                    Data::PARENT_LOCATION => ''
                ],
            self::BILLING_ADDRESS_BEFORE_PLACE_ORDER[Data::LOCATION_ID] =>
                [
                    Data::LOCATION => self::BILLING_ADDRESS_BEFORE_PLACE_ORDER[Data::LOCATION],
                    Data::PARENT_LOCATION => self::BILLING_ADDRESS_BEFORE_PLACE_ORDER[Data::PARENT_LOCATION]
                ],
            self::BILLING_ADDRESS_PAYMENT_AFTER_METHODS[Data::LOCATION_ID] =>
                [
                    Data::LOCATION => self::BILLING_ADDRESS_PAYMENT_AFTER_METHODS[Data::LOCATION],
                    Data::PARENT_LOCATION => ''
                ],
            self::BILLING_ADDRESS_PAYMENT_BEFORE_METHODS[Data::LOCATION_ID] =>
                [
                    Data::LOCATION => self::BILLING_ADDRESS_PAYMENT_BEFORE_METHODS[Data::LOCATION],
                    Data::PARENT_LOCATION => ''
                ]
        ];
    }
}
