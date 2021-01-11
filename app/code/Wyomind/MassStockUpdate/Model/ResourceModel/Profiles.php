<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Model\ResourceModel;

/**
 * Class Profiles
 * @package Wyomind\MassStockUpdate\Model\ResourceModel
 */
class Profiles extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var string
     */
    public $module = "massstockupdate";

    /**
     * @param $request
     * @return \Zend_Db_Statement_Interface
     */
    public function importProfile($request)
    {


        $connection = $this->getConnection('write');
        $request = str_replace("{{table}}", $this->getTable("" . $this->module . "_profiles"), $request);
        return $connection->query($request);

    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init($this->module . '_profiles', 'id');
    }
}
