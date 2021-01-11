<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Sources/FieldRequired.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Sources;

/**
 * Class InputType
 * @package Xtento\CustomAttributes\ModelSource
 */
class FieldRequired extends AbstractSource
{
    const YES = 1;
    const NO = 0;
    const FRONTEND_ONLY = 2;

    public function getAvailable()
    {
        return [
            self::YES => __('Yes'),
            self::NO => __('No'),
            self::FRONTEND_ONLY => __('On the frontend only'),
        ];
    }
}
