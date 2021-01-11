<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-02-13T21:40:17+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Sources/InputType.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Sources;

/**
 * Class InputType
 * @package Xtento\CustomAttributes\ModelSource
 */
class InputType extends AbstractSource
{
    const TEXT = 'text';
    const TEXT_AREA = 'textarea';
    const DATE = 'date';
    const DATETIME = 'datetime';
    const BOOLEAN = 'boolean';
    const MULTI_SELECT = 'multiselect';
    const SELECT = 'select';
    const FILE = 'file';

    public function getAvailable()
    {
        return [
            self::TEXT => __('Text Field'),
            self::TEXT_AREA => __('Text Area'),
            self::DATE => __('Date / Date & Time'),
            self::BOOLEAN => __('Yes/No (or Checkbox)'),
            self::SELECT => __('Dropdown / Select / Radio Buttons'),
            self::MULTI_SELECT => __('Multi Select / Multi Checkboxes'),
            self::FILE => __('File Upload')
        ];
    }
}
