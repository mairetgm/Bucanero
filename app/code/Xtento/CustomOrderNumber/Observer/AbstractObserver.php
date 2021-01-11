<?php

/**
 * Product:       Xtento_CustomOrderNumber
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2020-07-20T14:22:51+00:00
 * File:          app/code/Xtento/CustomOrderNumber/Observer/AbstractObserver.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomOrderNumber\Observer;

use Magento\Sales\Model\EntityInterface;

abstract class AbstractObserver
{
    const TYPE_INVOICE = 'invoice';
    const TYPE_SHIPMENT = 'shipment';
    const TYPE_CREDITMEMO = 'creditmemo';

    /**
     * @var \Xtento\CustomOrderNumber\Helper\Config
     */
    protected $configHelper;

    /**
     * @var \Xtento\CustomOrderNumber\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Xtento\CustomOrderNumber\Helper\Generator
     */
    protected $incrementIdGenerator;

    /**
     * @var \Magento\SalesSequence\Model\Manager
     */
    protected $sequenceManager;

    /**
     * AbstractObserver constructor.
     *
     * @param \Xtento\CustomOrderNumber\Helper\Config $configHelper
     * @param \Xtento\CustomOrderNumber\Helper\Module $moduleHelper
     * @param \Xtento\CustomOrderNumber\Helper\Generator $incrementIdGenerator
     * @param \Magento\SalesSequence\Model\Manager $sequenceManager
     */
    public function __construct(
        \Xtento\CustomOrderNumber\Helper\Config $configHelper,
        \Xtento\CustomOrderNumber\Helper\Module $moduleHelper,
        \Xtento\CustomOrderNumber\Helper\Generator $incrementIdGenerator,
        \Magento\SalesSequence\Model\Manager $sequenceManager
    ) {
        $this->configHelper = $configHelper;
        $this->moduleHelper = $moduleHelper;
        $this->incrementIdGenerator = $incrementIdGenerator;
        $this->sequenceManager = $sequenceManager;
    }

    /**
     * @param $object
     * @return mixed
     */
    abstract public function getCollectionForOrder($object);

    /**
     * If "use same number as order number" is used, set the order increment_id for object
     *
     * @param $object
     * @param $entityType
     * @return $this
     */
    protected function updateIncrementId($object, $entityType)
    {
        if (!$this->moduleHelper->isModuleEnabled()) {
            return $this;
        }

        if (!$object->getId()) {
            /** @var $order \Magento\Sales\Model\Order */
            $order = $object->getOrder();
            $storeId = $order->getStoreId();

            // Is (order/invoice/...) number customizer enabled for this store ID?
            if (!$this->configHelper->getConfigFlag($entityType, 'enabled', $storeId)) {
                return $this;
            }

            // Shall the order number be used? Just for invoice/shipment/credit memo numbers
            if (!$this->configHelper->getConfigFlag($entityType, 'same_as_order', $storeId)) {
                // Generate new increment ID
                if ($object instanceof EntityInterface && $object->getIncrementId() == null) {
                    $originalSequence = $this->sequenceManager->getSequence(
                        $object->getEntityType(),
                        $object->getStore()->getGroup()->getDefaultStoreId()
                    )->getNextValue();
                    $incrementId = $this->incrementIdGenerator->generateIncrementIdWithLock($object, $entityType, $originalSequence);
                    $object->setIncrementId($incrementId);
                }
                // Then, return, as otherwise the object ID would be set to the same ID as the order number, which is done in the following code
                return $this;
            }

            $orderIncrementId = $order->getIncrementId();
            $numberPrefix = $this->configHelper->getConfigValue($entityType, 'id_prefix', $storeId);
            $replaceInId = $this->configHelper->getConfigValue($entityType, 'replace_in_id', $storeId);
            if (!empty($replaceInId)) {
                $orderIncrementId = str_replace($replaceInId, "", $orderIncrementId);
            }
            if (empty($orderIncrementId)) {
                return $this;
            }

            // Get invoice/shipment/credit memo collection
            $collection = $this->getCollectionForOrder($order);

            $maxIterations = 99;
            $newIncrementId = false;
            $subIncrementIdCounter = 0;
            while ($newIncrementId === false) {
                if ($subIncrementIdCounter > $maxIterations) {
                    break;
                }
                if ($subIncrementIdCounter > 0) {
                    $newIncrementId = $numberPrefix . $orderIncrementId . '-' . $subIncrementIdCounter;
                } else {
                    $newIncrementId = $numberPrefix . $orderIncrementId;
                }
                $collection->clear();
                $collection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
                $collection->getSelect()->where('increment_id = ?', $newIncrementId);
                if ($collection->count() > 0) {
                    $newIncrementId = false;
                    $subIncrementIdCounter++;
                } else {
                    $object->setIncrementId($newIncrementId);
                    break;
                }
            }
        }
        return $this;
    }

}
