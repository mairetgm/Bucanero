<?php

/**
 * Product:       Xtento_PdfCustomizer
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2019-02-05T17:13:45+00:00
 * File:          app/code/Xtento/PdfCustomizer/Model/Source/AbstractSource.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\PdfCustomizer\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

abstract class AbstractSource implements OptionSourceInterface
{

    /**
     * Statuses
     */
    const IS_DEFAULT = 1;

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
