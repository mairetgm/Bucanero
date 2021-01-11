<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/CustomAttributes.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model;

use Xtento\CustomAttributes\Model\Order\LastOrderData;

/**
 * Class CustomAttributes
 * @package Xtento\CustomAttributes\Model
 */
class CustomAttributes
{
    const ADDRESS_ENTITY = 'customer_address';
    const CUSTOMER_ENTITY = 'customer';
    const ORDER_ENTITY = 'order_field';

    /**
     * After some tests it seems:
     *  - customer_register_address is used in the checkout for unregistered users
     *  - to pass the address items to customer we need "to_customer" attribute in a fieldset
     */
    const USED_IN = [
        'used_in_forms' => [
            'adminhtml_customer_address',
            'adminhtml_customer',
            'adminhtml_checkout',
            'checkout_register',
            'customer_account_create',
            'customer_account_edit',
            'customer_register_address',
            'customer_address_edit'
        ]
    ];

    const USED_IN_NONE = [
        'used_in_forms' => [
        ]
    ];

    private $lastOrderData;

    public function __construct(
        LastOrderData $lastOrderData
    ) {
        $this->lastOrderData = $lastOrderData;
    }

    public function lastOrderData()
    {
        return $this->lastOrderData->getOrderData();
    }
}
