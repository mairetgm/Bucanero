<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Sources/FieldType.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Sources;

use Xtento\CustomAttributes\Model\CustomAttributes;
use Magento\Customer\Model\Customer;

/**
 * Class FieldType
 * @package Xtento\CustomAttributes\ModelSource
 */
class FieldType extends AbstractSource
{
    /**
     * @return array
     */
    public function getAvailable()
    {
        $customer = Customer::ENTITY;
        $customerAddress = CustomAttributes::ADDRESS_ENTITY;
        $order = CustomAttributes::ORDER_ENTITY;

        return [
            $order => __(ucfirst(str_replace(['_', 'field'], [' ', ''], $order))) . ' ' . __('Attribute'),
            $customer => __(ucfirst($customer)) . ' ' . __('Attribute'),
            $customerAddress => __(ucwords(str_replace('_', ' ', $customerAddress))) . ' ' . __('Attribute'),
        ];
    }
}
