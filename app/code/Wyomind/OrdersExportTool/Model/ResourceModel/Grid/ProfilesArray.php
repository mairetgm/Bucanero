<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Model\ResourceModel\Grid;

class ProfilesArray implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    public function toOptionArray()
    {
        $profiles = [];
        $profiles[] = __('Not exported');
        foreach ($this->_collectionFactory as $profile) {
            $profiles[] = ['value' => $profile->getId(), 'label' => $profile->getName()];
        }
        return $profiles;
    }
}