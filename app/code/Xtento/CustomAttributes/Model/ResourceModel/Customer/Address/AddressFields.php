<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:03+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/ResourceModel/Customer/Address/AddressFields.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\ResourceModel\Customer\Address;

use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Helper\FieldTemplates;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\Sources\FieldRequired;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Source\Table as AttributeSourceTable;
use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\ResourceModel\Entity\Type;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Attribute as AttributeModel;

/**
 * Class CustomerFields
 * @package Xtento\CustomAttributes\Model\ResourceModel\Customer
 */
class AddressFields extends Attribute
{
    /**
     * @var SalesSetupFactory
     */
    private $salesSetup;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetup;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetup;

    /**
     * @var Fields
     */
    private $fieldAttribute;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var AttributeModel
     */
    private $attributeModel;

    /**
     * CustomerFields constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Type $eavEntityType
     * @param SalesSetupFactory $salesSetup
     * @param QuoteSetupFactory $quoteSetup
     * @param ModuleDataSetupInterface $setup
     * @param CustomerSetupFactory $customerSetup
     * @param EavSetupFactory $eavSetup
     * @param ManagerInterface $messageManager
     * @param string $connectionName
     * @param AttributeModel $attributeModel
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Type $eavEntityType,
        SalesSetupFactory $salesSetup,
        QuoteSetupFactory $quoteSetup,
        ModuleDataSetupInterface $setup,
        CustomerSetupFactory $customerSetup,
        EavSetupFactory $eavSetup,
        ManagerInterface $messageManager,
        AttributeModel $attributeModel
    ) {
        $this->salesSetup     = $salesSetup;
        $this->setup          = $setup;
        $this->customerSetup  = $customerSetup;
        $this->quoteSetup     = $quoteSetup;
        $this->messageManager = $messageManager;
        $this->eavSetup       = $eavSetup;
        $this->attributeModel = $attributeModel;

        parent::__construct(
            $context,
            $storeManager,
            $eavEntityType
        );
    }

    /**
     * @param $fieldAttribute
     * @return Fields
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function entryPoint($fieldAttribute)
    {
        $this->fieldAttribute = $fieldAttribute;

        $fieldAttributeId = $fieldAttribute->getId();
        $fieldAttributeForDelete = $fieldAttribute->getData(Data::ACTION);

        if ($fieldAttributeId && !$fieldAttributeForDelete) {
            $this->updateAttribute();
            return $this->fieldAttribute;
        }

        if ($fieldAttributeId && $fieldAttributeForDelete) {
            $this->removeAttribute();
            $this->removeColumns();
            return $this->fieldAttribute;
        }

        $this->addNewAttribute();
        return $this->fieldAttribute;
    }

    /**
     * @return $this|mixed
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addNewAttribute()
    {
        $setup = $this->setup;
        /** @var Fields $field */
        $field = $this->fieldAttribute;

        $frontEndInput = $field->getFrontendInput();

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetup->create(['setup' => $setup]);
        $attributeData = array_replace_recursive(FieldTemplates::DEFAULT_ATTRIBUTE_DATA, $field->getData());

        if ($frontEndInput == 'select' || $frontEndInput == 'multiselect') {
            $attributeData['source'] = AttributeSourceTable::class;
            $attributeData['backend'] = ArrayBackend::class;
        }

        $attributeData['input'] = $frontEndInput;
        $attributeData['formElement'] = $frontEndInput;

        $fieldRequire = $field->getFieldRequired();
        $attributeData['is_required'] = (bool)$fieldRequire;

        if ($fieldRequire == FieldRequired::FRONTEND_ONLY) {
            $attributeData['is_required'] = false;
        }

        $usedIn = [
            'used_in_forms' => explode(',', $field->getUsedInForms())
        ];
        $isEnable = $field->getIsActive();
        if (!$isEnable) {
            $attributeData['is_required'] = false;
            $usedIn = CustomAttributes::USED_IN_NONE;
        }

        $attributeData['frontend'] = $frontEndInput;

        $backendType = $this->attributeModel->getBackendTypeByInput($frontEndInput);
        $attributeData['backend_type'] = $backendType;

        $defaultValueByInput = $this->attributeModel->getDefaultValueByInput($frontEndInput);
        if ($defaultValueByInput) {
            $attributeData['default_value'] = $field->getData($defaultValueByInput);
        }

        /** @var \Magento\Eav\Model\Config $config */
        $config = $customerSetup->getEavConfig();
        $entityType = $this->eavSetup->create()->getEntityTypeId(CustomAttributes::ADDRESS_ENTITY);
        $attribute = $config->getAttribute($entityType, $attributeData[Data::FIELD_IDENTIFIER]);
        $attribute->addData($attributeData);
        $attribute->addData($usedIn);

        $this->addWithEavSetup($attributeData[Data::FIELD_IDENTIFIER], $frontEndInput);
        $attributeId = $this->getIdByCode(CustomAttributes::ADDRESS_ENTITY, $attributeData[Data::FIELD_IDENTIFIER]);
        $attribute->setId($attributeId);

        try {
            $this->save($attribute);
        } catch (CouldNotSaveException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this;
        }

        return $attribute->getId();
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateAttribute()
    {
        $setup = $this->setup;
        /** @var Fields $field */
        $field = $this->fieldAttribute;

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetup->create(['setup' => $setup]);
        $attributeId = $this->getIdByCode(CustomAttributes::ADDRESS_ENTITY, $field->getAttributeCode());
        $attributeCode = $this->attributeCodeById($attributeId);

        /** @var \Magento\Eav\Model\Config $config */
        $config = $customerSetup->getEavConfig();
        $attribute = $config->getAttribute(CustomAttributes::ADDRESS_ENTITY, $attributeCode);
        $attributeData = array_replace_recursive($attribute->getData(), $field->getData());

        $defaultValueByInput = $this->attributeModel->getDefaultValueByInput($attribute->getFrontendInput());
        if ($defaultValueByInput) {
            $attributeData['default_value'] = $field->getData($defaultValueByInput);
        }

        $frontEndInput = $field->getFrontendInput();
        $attributeData['input'] = $frontEndInput;
        $attributeData['formElement'] = $frontEndInput;

        $fieldRequire = $field->getFieldRequired();
        $attributeData['is_required'] = (bool)$fieldRequire;

        if ($fieldRequire == FieldRequired::FRONTEND_ONLY){
            $attributeData['is_required'] = false;
        }

        $usedIn = [
            'used_in_forms' => explode(',', $field->getUsedInForms())
        ];
        $isEnable = $field->getIsActive();
        if (!$isEnable) {
            $attributeData['is_required'] = false;
            $usedIn = CustomAttributes::USED_IN_NONE;
        }

        $attribute->setData($attributeData);
        $attribute->addData($usedIn);

        try {
            $this->save($attribute);
        } catch (CouldNotSaveException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function removeAttribute()
    {
        $setup = $this->setup;
        /** @var EavSetup $eavSetup */
        $field = $this->fieldAttribute;
        $attributeCode = $this->attributeCodeById($field->getData('attribute_id'));

        $customerSetup = $this->customerSetup->create(['setup' => $setup]);
        $config = $customerSetup->getEavConfig();
        $attribute = $config->getAttribute(CustomAttributes::ADDRESS_ENTITY, $attributeCode);

        try {
            $this->delete($attribute);
        } catch (CouldNotSaveException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $this;
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function _afterSave(AbstractModel $object)
    {
        $field = $this->fieldAttribute;
        $fieldAttributeId = $field->getId();
        $fieldAttributeForDelete = $field->getData(Data::ACTION);

        $attributeId = $object->getId();
        $this->fieldAttribute->setData('attribute_id', $attributeId);

        if ($fieldAttributeId && !$fieldAttributeForDelete) {
            $this->updateColumns();
            return parent::_afterSave($object);
        }

        if ($fieldAttributeId && $fieldAttributeForDelete) {
            $this->removeColumns();
            return $this;
        }

        $this->addNewColumns();
        return parent::_afterSave($object);
    }

    /**
     * This will add the proper relations for the attribute
     *
     * @param string $attributeCode
     * @param string $frontEndInput
     */
    private function addWithEavSetup($attributeCode, $frontEndInput)
    {
        $minimalData = FieldTemplates::DEFAULT_ATTRIBUTE_DATA;
        $minimalData[Data::FIELD_IDENTIFIER] = $attributeCode;

        if ($frontEndInput == 'select' || $frontEndInput == 'multiselect') {
            $minimalData['source'] = AttributeSourceTable::class;
            $minimalData['backend'] = ArrayBackend::class;
        }

        $minimalData['input'] = $frontEndInput;
        $minimalData['formElement'] = $frontEndInput;

        $this->eavSetup->create()->addAttribute(
            CustomAttributes::ADDRESS_ENTITY,
            $attributeCode,
            $minimalData
        );
    }

    /**
     * Add columns
     */
    private function addNewColumns()
    {
        $field = $this->fieldAttribute;

        $this->addCommitCallback(function () use ($field) {

            $quoteInstaller = $this->quoteSetup->create(
                ['resourceName' => 'quote_setup', 'setup' => $this->setup]
            );

            $quoteInstaller->addAttribute(
                'quote_address',
                $field->getAttributeCode(),
                FieldTemplates::QUOTE_ATTRIBUTES[$field->getFrontendInput()]
            );

            /** @var SalesSetup $salesInstaller */
            $salesInstaller = $this->salesSetup->create(
                ['resourceName' => 'sales_setup', 'setup' => $this->setup]
            );

            $salesInstaller->addAttribute(
                'order_address',
                $field->getAttributeCode(),
                FieldTemplates::ORDER_ATTRIBUTES[$field->getFrontendInput()]
            );

            /** @var SalesSetup $salesInstaller */
            $salesInstaller->getConnection()->addColumn(
                $this->getTable('sales_order_grid'),
                $field->getAttributeCode(),
                Table::TYPE_TEXT
            );

            /** @var SalesSetup $salesInstaller */
            $salesInstaller->getConnection()->addColumn(
                $this->getTable('sales_order'),
                $field->getAttributeCode(),
                Table::TYPE_TEXT
            );
        });
    }

    /**
     * Update the columns
     * note tested
     */
    private function updateColumns()
    {
        $field = $this->fieldAttribute;

        $this->addCommitCallback(function () use ($field) {

            $quoteInstaller = $this->quoteSetup->create(
                ['resourceName' => 'quote_setup', 'setup' => $this->setup]
            );

            $quoteInstaller->updateAttribute(
                'quote_address',
                $field->getAttributeCode(),
                FieldTemplates::QUOTE_ATTRIBUTES[$field->getFrontendInput()]
            );

            /** @var SalesSetup $salesInstaller */
            $salesInstaller = $this->salesSetup->create(
                ['resourceName' => 'sales_setup', 'setup' => $this->setup]
            );

            $salesInstaller->updateAttribute(
                'order_address',
                $field->getAttributeCode(),
                FieldTemplates::ORDER_ATTRIBUTES[$field->getFrontendInput()]
            );

            $salesInstaller->updateAttribute(
                'sales_order',
                $field->getAttributeCode(),
                FieldTemplates::ORDER_ATTRIBUTES[$field->getFrontendInput()]
            );
        });
    }

    /**
     * Remove the related columns
     */
    private function removeColumns()
    {
        $field = $this->fieldAttribute;

        $this->addCommitCallback(function () use ($field) {

            $this->getConnection()->dropColumn(
                $this->getTable('quote_address'),
                $field->getAttributeCode()
            );

            $this->getConnection()->dropColumn(
                $this->getTable('sales_order_address'),
                $field->getAttributeCode()
            );

            $this->getConnection()->dropColumn(
                $this->getTable('sales_order_grid'),
                $field->getAttributeCode()
            );

            $this->getConnection()->dropColumn(
                $this->getTable('sales_order'),
                $field->getAttributeCode()
            );
        });

        return $this;
    }

    /**
     * Get a attribute code by id
     *
     * @param $entityAttributeId
     * @return string
     */
    private function attributeCodeById($entityAttributeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('eav_attribute'),
            'attribute_code'
        )->where(
            'attribute_id = ?',
            (int)$entityAttributeId
        );
        return $this->getConnection()->fetchOne($select);
    }
}
