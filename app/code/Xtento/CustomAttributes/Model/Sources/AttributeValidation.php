<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Sources/AttributeValidation.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Sources;

/**
 * Class InputType
 * @package Xtento\CustomAttributes\ModelSource
 */
class AttributeValidation extends AbstractSource
{
    const ATTRIBUTE_VALIDATION = 1;

    public function getAvailable()
    {
        return [
            self::ATTRIBUTE_VALIDATION => __('test'),
        ];
    }
}
