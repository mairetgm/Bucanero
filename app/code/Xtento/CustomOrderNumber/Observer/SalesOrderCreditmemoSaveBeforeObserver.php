<?php

/**
 * Product:       Xtento_CustomOrderNumber
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2016-01-27T15:35:28+00:00
 * File:          app/code/Xtento/CustomOrderNumber/Observer/SalesOrderCreditmemoSaveBeforeObserver.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomOrderNumber\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderCreditmemoSaveBeforeObserver extends AbstractObserver implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->updateIncrementId($observer->getCreditmemo(), self::TYPE_CREDITMEMO);
    }

    /**
     * @param $object \Magento\Sales\Model\Order
     * @return \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection
     */
    public function getCollectionForOrder($object)
    {
        return $object->getCreditmemosCollection();
    }
}
