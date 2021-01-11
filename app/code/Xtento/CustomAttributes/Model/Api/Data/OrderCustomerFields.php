<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Api/Data/OrderCustomerFields.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Api\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Xtento\CustomAttributes\Api\Data\OrderCustomerFieldsInterface;

class OrderCustomerFields extends AbstractSimpleObject implements OrderCustomerFieldsInterface
{

    const ORDER_FIELD_NAME = 'order_fields';

    /**
     * @return string|null
     */
    public function getFields()
    {
        return $this->_get(self::ORDER_FIELD_NAME);
    }

    /**
     * @param string $fields
     * @return $this
     */
    public function setFields($fields)
    {
        return $this->setData(self::ORDER_FIELD_NAME, $fields);
    }
}