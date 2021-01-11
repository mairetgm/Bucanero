<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Xtea/Edit/Tab/Options.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Registry;
use Magento\Framework\Validator\UniversalFactory;

class Options extends \Magento\Backend\Block\Template
{
    private $registry;

    private $attributeRepository;

    private $attrOptionCollectionFactory;

    private $universalFactory;

    private $dataObjectFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        AttributeRepository $attributeRepository,
        UniversalFactory $universalFactory,
        CollectionFactory $attrOptionCollectionFactory,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->registry                    = $registry;
        $this->attributeRepository         = $attributeRepository;
        $this->universalFactory            = $universalFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->dataObjectFactory           = $dataObjectFactory;

        parent::__construct($context, $data);
        $this->_template = 'Xtento_CustomAttributes::catalog/product/attribute/options.phtml';
    }

    public function canManageOptionDefaultOnly()
    {
        $attribute = $this->getAttributeObject();

        if (!$attribute) {
            return false;
        }

        $canManage = !$attribute->getCanManageOptionLabels();
        $isUserDefined = !$attribute->getIsUserDefined();
        $sourceModel = $attribute->getSourceModel();

        $verdict = $canManage && $isUserDefined && $sourceModel;

        return $verdict;
    }

    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores(true));
        }
        return $this->_getData('stores');
    }

    public function getStoresSortedBySortOrder()
    {
        $stores = $this->getStores();
        if (is_array($stores)) {
            usort($stores, function ($storeA, $storeB) {
                if ($storeA->getSortOrder() == $storeB->getSortOrder()) {
                    return $storeA->getId() < $storeB->getId() ? -1 : 1;
                }
                return ($storeA->getSortOrder() < $storeB->getSortOrder()) ? -1 : 1;
            });
        }
        return $stores;
    }

    public function getOptionValues()
    {
        $values = $this->_getData('option_values');
        if ($values === null) {
            $values = [];

            $attribute = $this->getAttributeObject();
            $optionCollection = $this->_getOptionValuesCollection($attribute);
            if ($optionCollection) {
                $values = $this->_prepareOptionValues($attribute, $optionCollection);
            }

            $this->setData('option_values', $values);
        }

        return $values;
    }

    private function _prepareOptionValues(
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        $optionCollection
    ) {
        $type = $attribute->getFrontendInput();
        if ($type === 'select' || $type === 'multiselect') {
            $defaultValues = explode(',', $attribute->getDefaultValue());
            $inputType = $type === 'select' ? 'radio' : 'checkbox';
        } else {
            $defaultValues = [];
            $inputType = '';
        }

        $values = [];
        $isSystemAttribute = is_array($optionCollection);
        foreach ($optionCollection as $option) {
            $bunch = $isSystemAttribute ? $this->prepareSystemAttributeOptionValues(
                $option,
                $inputType,
                $defaultValues
            ) : $this->prepareUserDefinedAttributeOptionValues(
                $option,
                $inputType,
                $defaultValues
            );
            foreach ($bunch as $value) {
                $values[] = new DataObject($value);
            }
        }

        return $values;
    }

    private function _getOptionValuesCollection($attribute)
    {
        if (!$attribute) {
            return null;
        }

        if ($this->canManageOptionDefaultOnly()) {
            $options = $this->universalFactory->create(
                $attribute->getSourceModel()
            )->setAttribute(
                $attribute
            )->getAllOptions();
            return $options;
        } else {
            $attrOptions = $this->attrOptionCollectionFactory->create()->setAttributeFilter(
                $attribute->getId()
            )->setPositionOrder(
                'asc',
                true
            )->load();

            return $attrOptions;
        }
    }

    private function prepareSystemAttributeOptionValues($option, $inputType, $defaultValues, $valuePrefix = '')
    {
        if (is_array($option['value'])) {
            $values = [];
            foreach ($option['value'] as $subOption) {
                $bunch = $this->prepareSystemAttributeOptionValues(
                    $subOption,
                    $inputType,
                    $defaultValues,
                    $option['label'] . ' / '
                );
                $values[] = $bunch[0];
            }
            return $values;
        }

        $value['checked'] = in_array($option['value'], $defaultValues) ? 'checked="checked"' : '';
        $value['intype'] = $inputType;
        $value['id'] = $option['value'];
        $value['sort_order'] = 0;

        foreach ($this->getStores() as $store) {
            $storeId = $store->getId();
            $value['store' . $storeId] = $storeId ==
            \Magento\Store\Model\Store::DEFAULT_STORE_ID ? $valuePrefix . $this->escapeHtml($option['label']) : '';
        }

        return [$value];
    }

    private function prepareUserDefinedAttributeOptionValues($option, $inputType, $defaultValues)
    {
        $optionId = $option->getId();

        $value['checked'] = in_array($optionId, $defaultValues) ? 'checked="checked"' : '';
        $value['intype'] = $inputType;
        $value['id'] = $optionId;
        $value['sort_order'] = $option->getSortOrder();

        foreach ($this->getStores() as $store) {
            $storeId = $store->getId();
            $storeValues = $this->getStoreOptionValues($storeId);
            $value['store' . $storeId] = isset(
                $storeValues[$optionId]
            ) ? $this->escapeHtml(
                $storeValues[$optionId]
            ) : '';
        }

        return [$value];
    }

    public function getStoreOptionValues($storeId)
    {
        $values = $this->getData('store_option_values_' . $storeId);
        if ($values === null) {
            $values = [];
            $valuesCollection = $this->attrOptionCollectionFactory->create()->setAttributeFilter(
                $this->getAttributeObject()->getId()
            )->setStoreFilter(
                $storeId,
                false
            )->load();
            foreach ($valuesCollection as $item) {
                $values[$item->getId()] = $item->getValue();
            }
            $this->setData('store_option_values_' . $storeId, $values);
        }
        return $values;
    }

    private function getAttributeObject()
    {
        $attribute = $this->registry->registry('custom_attribute_data');

        if ($attribute) {
            return $attribute;
        }

        return false;
    }
}
