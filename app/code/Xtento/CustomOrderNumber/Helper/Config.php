<?php

/**
 * Product:       Xtento_CustomOrderNumber
 * ID:            TP2Z1gIjMryzjs+kTRDh6aWTwEp5w7T8imVFGAtG5js=
 * Last Modified: 2016-01-05T11:53:19+00:00
 * File:          app/code/Xtento/CustomOrderNumber/Helper/Config.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomOrderNumber\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    protected $coreDataCollectionFactory;

    /**
     * @var Magento\Framework\App\Config\DataFactory
     */
    protected $coreValueFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $coreDataCollectionFactory
     * @param \Magento\Framework\App\Config\ValueFactory $coreValueFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $coreDataCollectionFactory,
        \Magento\Framework\App\Config\ValueFactory $coreValueFactory
    ) {
        $this->storeManager = $storeManager;
        $this->coreDataCollectionFactory = $coreDataCollectionFactory;
        $this->coreValueFactory = $coreValueFactory;
        parent::__construct($context);
    }

    public function isSupportedEntityType($entityType)
    {
        $supportedEntityTypes = ['order', 'invoice', 'shipment', 'creditmemo'];
        return in_array($entityType, $supportedEntityTypes);
    }

    public function getConfigValue($entityType, $field, $storeId)
    {
        return $this->scopeConfig->getValue(
            'customordernumber/' . $entityType . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getConfigFlag($entityType, $field, $storeId)
    {
        return $this->scopeConfig->isSetFlag(
            'customordernumber/' . $entityType . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getConfigValueFromDb($entityType, $field, $storeId)
    {
        // Determine scope
        $scopeId = 0;
        $scope = 'default';
        if ($this->getConfigFlag($entityType, 'unique_per_store', $storeId)) {
            $scopeId = $storeId;
            $scope = 'stores';
        }
        if ($this->getConfigFlag($entityType, 'unique_per_website', $storeId)) {
            $scopeId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $scope = 'websites';
        }

        // Avoid cache by getting the data via the collection directly
        /** @var $collection \Magento\Config\Model\ResourceModel\Config\Data\Collection */
        $configDataCollection = $this->coreDataCollectionFactory->create();
        $configDataCollection = $configDataCollection
            ->addFieldToFilter('path', 'customordernumber/' . $entityType . '/' . $field)
            ->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('scope_id', $scopeId)
            ->setPageSize(1);

        if ($configDataCollection->count() > 0) {
            return $configDataCollection->getFirstItem();
        } else {
            $configData = $this->coreValueFactory->create()
                ->setPath('customordernumber/' . $entityType . '/' . $field)
                ->setScope($scope)
                ->setScopeId($scopeId);
            return $configData;
        }
    }

    public function prepareIntPositive($val)
    {
        $val = intval($val);
        if ($val < 0) {
            return 0;
        } else {
            return $val;
        }
    }
}
