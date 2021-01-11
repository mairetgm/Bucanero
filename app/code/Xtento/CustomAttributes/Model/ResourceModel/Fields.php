<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-04-09T13:52:30+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/ResourceModel/Fields.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\ResourceModel;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Helper\DataFactory;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Setup\InstallSchema;
use Xtento\CustomAttributes\Model\ResourceModel\Order\OrderFields;
use Xtento\CustomAttributes\Model\ResourceModel\Customer\CustomerFields;
use Xtento\CustomAttributes\Model\ResourceModel\Customer\Address\AddressFields;
use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Fields
 * @package Xtento\CustomAttributes\Model\ResourceModel
 */
class Fields extends AbstractDb
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var OrderFields
     */
    private $orderFields;

    /**
     * @var CustomerFields
     */
    private $customerFields;

    /**
     * @var AddressFields
     */
    private $addressFields;

    private $dataHelper;

    /**
     * Fields constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param OrderFields $orderFields
     * @param CustomerFields $customerFields
     * @param AddressFields $addressFields;
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        OrderFields $orderFields,
        CustomerFields $customerFields,
        AddressFields $addressFields,
        DataFactory $dataHelper,
        string $connectionName = null
    ) {
        $this->storeManager   = $storeManager;
        $this->dateTime       = $dateTime;
        $this->entityManager  = $entityManager;
        $this->metadataPool   = $metadataPool;
        $this->orderFields    = $orderFields;
        $this->customerFields = $customerFields;
        $this->addressFields  = $addressFields;
        $this->dataHelper     = $dataHelper;

        parent::__construct(
            $context,
            $connectionName
        );
    }

    public function _construct()
    {
        $this->_init(InstallSchema::TABLE, FieldsInterface::ENTITY_ID);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);

            $object = $this->dataHelper->create()->addAttributeData($object);
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _beforeSave(AbstractModel $object)
    {
        $columnExists = $this->reservedCodes();

        $object->setAttributeCode(strtolower($object->getAttributeCode()));

        if (in_array($object->getAttributeCode(), $columnExists) && !$object->getId()) {
            $message = __('The attribute code already exists or is reserved');
            throw new LocalizedException(
                $message
            );
        }

        if ($object->getData(Data::TYPE_ID) == Customer::ENTITY) {
            /** @var \Xtento\CustomAttributes\Model\Fields $object */
            $result = $this->customerFields->entryPoint($object);
            $object->setAttributeId($result->getData('attribute_id'));
        }

        if ($object->getData(Data::TYPE_ID) == CustomAttributes::ORDER_ENTITY) {
            /** @var \Xtento\CustomAttributes\Model\Fields $object */
            $result = $this->orderFields->entryPoint($object);
            $object->setAttributeId($result->getData('attribute_id'));
        }

        if ($object->getData(Data::TYPE_ID) == CustomAttributes::ADDRESS_ENTITY) {
            /** @var \Xtento\CustomAttributes\Model\Fields $object */
            $result = $this->addressFields->entryPoint($object);
            $object->setAttributeId($result->getData('attribute_id'));
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function _afterSave(AbstractModel $object)
    {
        $fieldAttributeId = $object->getId();
        $fieldAttributeForDelete = $object->getData(Data::ACTION);
        if ($fieldAttributeId && $fieldAttributeForDelete) {
            $this->delete($object);
            return $this;
        }

        $this->saveStoreRelation($object);
        return parent::_afterSave($object);
    }

    /**
     * @param $field
     * @return array
     */
    public function lookupStoreIds($field)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->getTable(InstallSchema::STORE_TABLE),
            'store_id'
        )->where(
            FieldsInterface::ENTITY_ID. ' = ?',
            (int)$field
        );

        return $adapter->fetchCol($select);
    }

    /**
     * @param AbstractModel $field
     * @return $this
     */
    public function saveStoreRelation(AbstractModel $field)
    {
        $oldStores = $this->lookupStoreIds($field->getId());
        $newStores = (array)$field->getStoreId();
        if (empty($newStores)) {
            $newStores = (array)$field->getStoreId();
        }
        $table = $this->getTable(InstallSchema::STORE_TABLE);

        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = [
                FieldsInterface::ENTITY_ID . ' = ?' => (int)$field->getId(),
                'store_id IN (?)' => $delete
            ];
            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    FieldsInterface::ENTITY_ID => (int)$field->getId(),
                    'store_id' => (int)$storeId
                ];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['entity_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable(InstallSchema::STORE_TABLE), $condition);
        return parent::_beforeDelete($object);
    }

    /**
     * @return array
     */
    private function reservedCodes()
    {
        $quote = $this->getTable('quote');
        $quoteColumns = array_keys($this->getConnection()->describeTable($quote));

        $quoteAddress = $this->getTable('quote_address');
        $quoteColumnsAddress = array_keys($this->getConnection()->describeTable($quoteAddress));

        $salesOrder = $this->getTable('sales_order');
        $salesOrderColumns = array_keys($this->getConnection()->describeTable($salesOrder));

        $salesOrderAddress = $this->getTable('sales_order_address');
        $salesOrderColumnsAddress = array_keys($this->getConnection()->describeTable($salesOrderAddress));

        return array_merge($quoteColumns, $quoteColumnsAddress, $salesOrderColumns, $salesOrderColumnsAddress);
    }
}
