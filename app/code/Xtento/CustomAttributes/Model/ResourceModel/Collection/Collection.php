<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-11-19T13:23:11+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/ResourceModel/Collection/Collection.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\ResourceModel\Collection;

use Magento\Framework\App\Area;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\Fields as FieldsModel;
use Xtento\CustomAttributes\Model\ResourceModel\Fields  as FieldsResourceModel;
use Xtento\CustomAttributes\Model\Sources\FieldRequired;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    //@codingStandardsIgnoreLine
    protected $_idFieldName = 'entity_id';

    /**
     * @var State
     */
    private $state;

    /**
     * Init resource model
     * @return void
     */

    public function _construct()
    {

        $this->_init(
            FieldsModel::class,
            FieldsResourceModel::class
        );

        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    // No constructor please, could cause issues with di:compile
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        State $state,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $metadataPool,
            $connection,
            $resource
        );
        $this->state = $state;
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }

        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function _afterLoad()
    {
        $this->performAfterLoad('xtento_attributes_field_store', 'entity_id');

        return parent::_afterLoad();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    public function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('xtento_attributes_field_store', 'entity_id');
    }

    /**
     * @param DataObject $item
     * @return DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAddLoadedItem(DataObject $item)
    {
        $required = $item->getData(FieldsInterface::FIELD_REQUIRED);
        if ($this->state->getAreaCode() === Area::AREA_FRONTEND && $required == FieldRequired::FRONTEND_ONLY) {
            $item->setData(FieldsInterface::FIELD_REQUIRED, 1);
        }
        return parent::beforeAddLoadedItem($item); // TODO: Change the autogenerated stub
    }
}