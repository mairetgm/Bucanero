<?php

namespace Wyomind\OrderUpdater\Model\ResourceModel;

class Profiles extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public $module = "orderupdater";

    protected function _construct()
    {
        $this->_init($this->module . '_profiles', 'id');
    }

    public function importProfile($request)
    {
        $connection = $this->getConnection('write');
        $request = str_replace("{{table}}", $this->getTable("" . $this->module . "_profiles"), $request);
        return $connection->query($request);

    }
}
