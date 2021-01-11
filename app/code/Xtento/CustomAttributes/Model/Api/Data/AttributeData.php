<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-06-18T14:03:46+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Api/Data/AttributeData.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Api\Data;

use Magento\Framework\Api\AbstractSimpleObject;
use Xtento\CustomAttributes\Api\Data\AttributeDataInterface;

class AttributeData extends AbstractSimpleObject implements AttributeDataInterface
{
    /**
     * Get attribute value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_get(self::VALUE);
    }

    /**
     * Set attribute value
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->_data[self::VALUE] = $value;
        return $this;
    }

}