<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-02-21T12:55:56+00:00
 * File:          app/code/Xtento/CustomAttributes/Api/CustomerOrderFieldsManagementInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Api;

use Xtento\Checkoutaddressfields\Api\Data\OrderFieldsInterface;
use Xtento\CustomAttributes\Api\Data\OrderCustomerFieldsInterface;

/**
 * Interface OrderFieldsManagementInterface
 * @package Xtento\Checkoutaddressfields\Api
 */
interface CustomerOrderFieldsManagementInterface
{
    /**
     * @param string $cartId
     * @param OrderCustomerFieldsInterface $fields
     *
     * @return string
     */
    public function saveFields(
        $cartId,
        OrderCustomerFieldsInterface $fields
    );

    /**
     * @param int $id The order ID.
     * @param OrderCustomerFieldsInterface $fields
     *
     * @return mixed
     */
    public function updateFields(
        $id,
        OrderCustomerFieldsInterface $fields
    );
}
