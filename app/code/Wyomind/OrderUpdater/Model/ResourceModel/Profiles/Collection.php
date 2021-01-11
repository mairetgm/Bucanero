<?php

namespace Wyomind\OrderUpdater\Model\ResourceModel\Profiles;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_init('Wyomind\OrderUpdater\Model\Profiles', 'Wyomind\OrderUpdater\Model\ResourceModel\Profiles');
    }

    public function getList($profilesIds)
    {
        if (!empty($profilesIds)) {
            $this->getSelect()->where("id IN (" . implode(',', $profilesIds) . ")");
        }
        return $this;
    }
}
