<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-07-01T14:08:30+00:00
 * File:          app/code/Xtento/CustomAttributes/Api/Data/AttributeDataInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Api\Data;

interface AttributeDataInterface
{
    const VALUE = 'value';

    /**
     * Return value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set value.
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value);
}