<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Model\Attribute\Source;

/**
 * Provide all options available for export to field
 */
class Export extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    public function getAllOptions()
    {
        if ($this->_options == null) {
            $this->_options[] = ["value" => null, "label" => __("not defined")];
            $mod = $this->_moduleList->getOne("Wyomind_OrdersExportTool");
            if ($mod != null) {
                foreach ($this->_profilesCollection as $profile) {
                    $this->_options[] = ['label' => $profile->getName() . " [" . $profile->getId() . "]", "value" => $profile->getId()];
                }
            }
        }
        return $this->_options;
    }
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}