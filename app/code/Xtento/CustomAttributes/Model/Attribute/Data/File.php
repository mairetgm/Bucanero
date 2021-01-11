<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-08-04T11:14:37+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Attribute/Data/File.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Attribute\Data;

class File extends \Magento\Customer\Model\Attribute\Data\File
{
    protected function _validateByRules($value)
    {
        $label = $this->getAttribute()->getStoreLabel();
        $rules = $this->getAttribute()->getValidateRules();
        $extension = pathinfo($value['name'], PATHINFO_EXTENSION);
        if (!empty($rules['file_extensions'])) {
            $extensions = explode(',', $rules['file_extensions']);
            $extensions = array_map('trim', $extensions);
            if (!in_array($extension, $extensions)) {
                return [__('"%1" is not a valid file extension.', $label)];
            }
        }
        /**
         * Check protected file extension
         */
        if (!$this->_fileValidator->isValid($extension)) {
            return $this->_fileValidator->getMessages();
        }
        if (!empty($rules['max_file_size'])) {
            $size = $value['size'];
            if ($rules['max_file_size'] < $size) {
                return [__('"%1" exceeds the allowed file size.', $label)];
            }
        }
        return [];
    }
}