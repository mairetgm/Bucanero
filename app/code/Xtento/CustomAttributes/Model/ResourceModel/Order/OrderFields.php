<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:04+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/ResourceModel/Order/OrderFields.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\ResourceModel\Order;

use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Helper\FieldTemplates;
use Xtento\CustomAttributes\Model\Fields;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Model\ResourceModel\Attribute;
use Magento\Eav\Setup\EavSetup;
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
use Magento\Eav\Model\Entity\Attribute\Source\Table as AttributeSourceTable;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;

/**
 * Class CustomerFields
 * @package Xtento\CustomAttributes\Model\ResourceModel\Customer
 */
class OrderFields extends Attribute
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
     * @param null $connectionName
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

        $attributeData['input'] = 'hidden';
        $attributeData['formElement'] = 'hidden';
        $attributeData['frontend'] = 'hidden';

        $defaultValueByInput = $this->attributeModel->getDefaultValueByInput($frontEndInput);
        if ($defaultValueByInput) {
            $attributeData['default_value'] = $field->getData($defaultValueByInput);
        }

        /** @var \Magento\Eav\Model\Config $config */
        $config = $customerSetup->getEavConfig();
        $entityType = $this->eavSetup->create()->getEntityTypeId(Customer::ENTITY);
        $attribute = $config->getAttribute($entityType, $attributeData[Data::FIELD_IDENTIFIER]);
        $attribute->addData($attributeData);
        $usedInForms = ['used_in_forms' => []];
        $attribute->addData($usedInForms);
        $attribute->setData('visible', false);

        $this->addWithEavSetup($attributeData[Data::FIELD_IDENTIFIER], $frontEndInput);
        $attributeId = $this->getIdByCode(Customer::ENTITY, $attributeData[Data::FIELD_IDENTIFIER]);
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
        $attributeId = $this->getIdByCode(Customer::ENTITY, $field->getAttributeCode());
        $attributeCode = $this->attributeCodeById($attributeId);

        /** @var \Magento\Eav\Model\Config $config */
        $config = $customerSetup->getEavConfig();
        $attribute = $config->getAttribute(Customer::ENTITY, $attributeCode);
        $attributeData = array_replace_recursive($attribute->getData(), $field->getData());

        $defaultValueByInput = $this->attributeModel->getDefaultValueByInput($attribute->getFrontendInput());
        if ($defaultValueByInput) {
            $attributeData['default_value'] = $field->getData($defaultValueByInput);
        }

        $attribute->setData('used_in_forms', []);
        $attribute->setData($attributeData);

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
        $attribute = $config->getAttribute(Customer::ENTITY, $attributeCode);

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

        $attributeData['input'] = 'hidden';
        $attributeData['formElement'] = 'hidden';
        $attributeData['frontend'] = 'hidden';
        $attributeData['visible'] = false;
        $attributeData['used_in_forms'] = [];

        $this->eavSetup->create()->addAttribute(Customer::ENTITY, $attributeCode, $minimalData);
    }

    /**
     * Add columns
     */
    public function addNewColumns()
    {
        $field = $this->fieldAttribute;

        $this->addCommitCallback(function () use ($field) {

            $quoteInstaller = $this->quoteSetup->create(
                ['resourceName' => 'quote_setup', 'setup' => $this->setup]
            );

            $quoteInstaller->addAttribute(
                'quote',
                $field->getAttributeCode(),
                FieldTemplates::QUOTE_ATTRIBUTES[$field->getFrontendInput()]
            );
            /** @var SalesSetup $salesInstaller */
            $salesInstaller = $this->salesSetup->create(
                ['resourceName' => 'sales_setup', 'setup' => $this->setup]
            );

            $salesInstaller->addAttribute(
                'order',
                $field->getAttributeCode(),
                FieldTemplates::ORDER_ATTRIBUTES[$field->getFrontendInput()]
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
                'quote',
                $field->getAttributeCode(),
                FieldTemplates::QUOTE_ATTRIBUTES[$field->getFrontendInput()]
            );
            /** @var SalesSetup $salesInstaller */
            $salesInstaller = $this->salesSetup->create(
                ['resourceName' => 'sales_setup', 'setup' => $this->setup]
            );

            $salesInstaller->updateAttribute(
                'order',
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
                $this->getTable('quote'),
                $field->getAttributeCode()
            );

            $this->getConnection()->dropColumn(
                $this->getTable('sales_order'),
                $field->getAttributeCode()
            );

            $this->getConnection()->dropColumn(
                $this->getTable('sales_order_grid'),
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