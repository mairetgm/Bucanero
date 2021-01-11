<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Sources/AbstractSource.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Sources;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AbstractSource
 * @package Xtento\CustomAttributes\Model\Source
 */
abstract class AbstractSource implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getAvailable();
        foreach ($availableOptions as $key => $value) {
            $options[$key] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
