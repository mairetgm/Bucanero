<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Model\ResourceModel\Product;

/**
 * Class Collection
 * @package Wyomind\MassStockUpdate\Model\ResourceModel\Product
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @param $identifierCode
     * @return $this
     */
    public function getSkuAndIdentifierCollection($identifierCode)
    {
        $this->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $this->getSelect()->columns(['entity_id', 'sku']);
        $this->addAttributeToSelect($identifierCode);
        return $this;

    }
}