<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Api/Data/OrderCustomerFieldsInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Api\Data;

/**
 * Interface OrderCustomerFieldsInterface
 * @package Xtento\Checkoutaddressfields\Api\Data
 */
interface OrderCustomerFieldsInterface
{
    /**
     * @return string[]|null
     */
    public function getFields();

    /**
     * @param string[] $fields
     * @return null
     */
    public function setFields($fields);
}
