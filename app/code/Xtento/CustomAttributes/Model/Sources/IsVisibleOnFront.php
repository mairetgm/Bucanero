<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-12-11T14:23:13+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Sources/IsVisibleOnFront.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Sources;

use Xtento\CustomAttributes\Helper\Data;

/**
 * Class InputType
 * @package Xtento\CustomAttributes\ModelSource
 */
class IsVisibleOnFront extends AbstractSource
{
    const IS_USED_ON_FRONT = 1;

    /**
     * @return array
     * @deprecated
     */
    public function getAvailable()
    {
        return [
            ['value' => '', 'label' => __('Hide in Frontend')],
            ['value' => Data::CHECKOUT, 'label' => __('Checkout')],
            ['value' => Data::ORDER_VIEW, 'label' => __('Order View')],
            ['value' => Data::REGISTRATION_FORM, 'label' => __('Customer Registration')],
            ['value' => Data::CUSTOMER_ACCOUNT, 'label' => __('Customer Account')]
        ];
    }
    public function toOptionArray()
    {
        $getAvailable = $this->getAvailable();

        return $getAvailable;
    }
}