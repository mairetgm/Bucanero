<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Model\ResourceModel\Profiles;

/**
 * Class Collection
 * @package Wyomind\MassStockUpdate\Model\ResourceModel\Profiles
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @param $profilesIds
     * @return $this
     */
    public function getList($profilesIds)
    {
        if (!empty($profilesIds)) {
            $this->getSelect()->where("id IN (" . implode(',', $profilesIds) . ")");
        }
        return $this;
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('Wyomind\MassStockUpdate\Model\Profiles', 'Wyomind\MassStockUpdate\Model\ResourceModel\Profiles');
    }
}
