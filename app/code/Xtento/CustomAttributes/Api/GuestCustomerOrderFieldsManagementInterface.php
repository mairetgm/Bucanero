<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-04-09T09:25:48+00:00
 * File:          app/code/Xtento/CustomAttributes/Api/GuestCustomerOrderFieldsManagementInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Api;

use Xtento\Checkoutaddressfields\Api\Data\OrderFieldsInterface;
use Xtento\CustomAttributes\Api\Data\OrderCustomerFieldsInterface;
use Xtento\CustomAttributes\Model\Api\Data\OrderCustomerFields;

/**
 * Interface GuestCustomerOrderFieldsManagementInterface
 * @package Xtento\Checkoutaddressfields\Api
 */
interface GuestCustomerOrderFieldsManagementInterface
{
    /**
     * @param string $cartId
     * @param OrderCustomerFieldsInterface $fields
     * @return string
     */
    public function saveFields(
        $cartId,
        OrderCustomerFieldsInterface $fields
    );
}
