<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Observer;

class SalesQuoteProductAddAfter implements \Magento\Framework\Event\ObserverInterface
{
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ids = [];
        foreach ($observer->getItems() as $item) {
            $ids[] = $item->getProductId();
        }
        $this->_productCollection->searchProducts($ids);
        foreach ($this->_productCollection as $product) {
            $profileId = $product->getExportTo();
            if ($profileId) {
                $item->setData('export_to', $profileId);
            } else {
                $item->setData('export_to', 0);
            }
        }
    }
}