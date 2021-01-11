<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:04+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Sources/UsedInForms.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Sources;

use Xtento\CustomAttributes\Model\CustomAttributes;

/**
 * Class InputType
 * @package Xtento\CustomAttributes\ModelSource
 */
class UsedInForms extends AbstractSource
{
    const USED_IN_FORMS = 1;

    /**
     * @param bool $entity
     *
     * @return array
     */
    public function getAvailable($entity = false)
    {
        if ($entity === CustomAttributes::ADDRESS_ENTITY) {
            return [
                ['value' => 'adminhtml_checkout', 'label' => __('Admin: Create Order')],
                ['value' => 'adminhtml_customer_address', 'label' => __('Admin: Edit Customer Address')],
                ['value' => 'checkout_register', 'label' => __('Checkout: Register')],
                ['value' => 'customer_register_address', 'label' => __('Customer: Add New Address')],
                ['value' => 'customer_address_edit', 'label' => __('Customer: Address Edit')]
            ];
        }
        if ($entity === CustomAttributes::CUSTOMER_ENTITY) {
            return [
                ['value' => 'adminhtml_checkout', 'label' => __('Admin: Create Order')],
                ['value' => 'adminhtml_customer', 'label' => __('Admin: Edit Customer')],
                ['value' => 'checkout_register', 'label' => __('Checkout: Register')],
                ['value' => 'customer_account_create', 'label' => __('Customer: Account Create')],
                ['value' => 'customer_account_edit', 'label' => __('Customer: Account Edit')],
            ];
        }

        return [
            ['value' => 'adminhtml_checkout', 'label' => __('Admin: Create Order')],
            ['value' => 'adminhtml_customer_address', 'label' => __('Admin: Edit Customer Address')],
            ['value' => 'adminhtml_customer', 'label' => __('Admin: Edit Customer')],
            ['value' => 'checkout_register', 'label' => __('Checkout: Register')],
            ['value' => 'customer_account_create', 'label' => __('Customer: Account Create')],
            ['value' => 'customer_account_edit', 'label' => __('Customer: Account Edit')],
            ['value' => 'customer_register_address', 'label' => __('Customer: Add New Address')],
            ['value' => 'customer_address_edit', 'label' => __('Customer: Address Edit')]
        ];
    }

    public function toOptionArray()
    {
        $getAvailable = $this->getAvailable();
        return $getAvailable;
    }
}
