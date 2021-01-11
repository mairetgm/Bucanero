<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-03-27T20:28:24+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Fields.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\ResourceModel\Fields as FieldsResourceModel;
use Magento\Framework\Model\AbstractModel;
use Xtento\CustomAttributes\Model\Sources\InputType;

class Fields extends AbstractModel implements FieldsInterface
{
    public function _construct()
    {
        $this->_init(FieldsResourceModel::class);
    }

    public function getId()
    {
        return $this->getData(FieldsInterface::ENTITY_ID);
    }

    public function getAttributeId()
    {
        return $this->getData(FieldsInterface::ATTRIBUTE_ID);
    }

    public function getAttributeTypeId()
    {
        return $this->getData(FieldsInterface::ATTRIBUTE_TYPE);
    }

    public function getStoreId()
    {
        return $this->getData(FieldsInterface::STORE_ID);
    }

    public function getCreatedAt()
    {
        return $this->getData(FieldsInterface::CREATED_AT);
    }

    public function getUpdatedAt()
    {
        return $this->getData(FieldsInterface::UPDATED_AT);
    }

    public function getIsActive()
    {
        return $this->getData(FieldsInterface::IS_ACTIVE);
    }

    /**
     * @return mixed
     *
     * @see \Xtento\CustomAttributes\Model\Sources\InputType
     */
    public function getFrontendInput()
    {
        return $this->getData(FieldsInterface::FRONTEND_INPUT);
    }

    public function getAttributeCode()
    {
        return $this->getData(FieldsInterface::ATTRIBUTE_CODE);
    }

    public function getFrontendClass()
    {
        return $this->getData(FieldsInterface::FRONTEND_CLASS);
    }

    public function getIsUsedInGrid()
    {
        return $this->getData(FieldsInterface::IS_USED_IN_GRID);
    }

    public function getUsedInForms()
    {
        return $this->getData(FieldsInterface::USED_IN_FORMS);
    }

    public function getCheckoutPosition()
    {
        return $this->getData(FieldsInterface::CHECKOUT_POSITION);
    }

    public function getIsVisibleOnFront()
    {
        return $this->getData(FieldsInterface::IS_VISIBLE_ON_FRONT);
    }

    public function getIsVisibleOnBack()
    {
        return $this->getData(FieldsInterface::IS_VISIBLE_ON_BACK);
    }

    public function getShowOnPdf()
    {
        return $this->getData(FieldsInterface::SHOW_ON_PDF);
    }

    public function getMaxLength()
    {
        return $this->getData(FieldsInterface::MAX_LENGTH);
    }

    public function getFrontendOption()
    {
        return $this->getData(FieldsInterface::FRONTEND_OPTION);
    }

    public function getShowLastValue()
    {
        return $this->getData(FieldsInterface::SHOW_LAST_VALUE);
    }

    public function getDisabledOnFrontend()
    {
        return $this->getData(FieldsInterface::DISABLED_ON_FRONTEND);
    }

    public function getCustomerGroups()
    {
        return $this->getData(FieldsInterface::CUSTOMER_GROUPS);
    }

    public function getTooltip()
    {
        return $this->getData(FieldsInterface::TOOLTIP);
    }

    public function getSaveSelected()
    {
        return $this->getData(FieldsInterface::SAVE_SELECTED);
    }

    public function getApplyDefault()
    {
        return $this->getData(FieldsInterface::APPLY_DEFAULT);
    }

    public function getAvailableOn()
    {
        return $this->getData(FieldsInterface::AVAILABLE_ON);
    }

    public function getAttributePosition()
    {
        return $this->getData(FieldsInterface::ATTRIBUTE_POSITION);
    }

    public function getFieldRequired()
    {
        return $this->getData(FieldsInterface::FIELD_REQUIRED);
    }

    public function setId($entityId)
    {
        $this->setData(FieldsInterface::ENTITY_ID, $entityId);
    }

    public function setAttributeId($attributeId)
    {
        $this->setData(FieldsInterface::ATTRIBUTE_ID, $attributeId);
    }

    public function setAttributeTypeId($attributeTypeId)
    {
        $this->setData(FieldsInterface::ATTRIBUTE_TYPE, $attributeTypeId);
    }

    public function setStoreId($storeId)
    {
        $this->setData(FieldsInterface::STORE_ID, $storeId);
    }

    public function setCreatedAt($createdAt)
    {
        $this->setData(FieldsInterface::CREATED_AT, $createdAt);
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->setData(FieldsInterface::UPDATED_AT, $updatedAt);
    }

    public function setIsActive($isActive)
    {
        $this->setData(FieldsInterface::IS_ACTIVE, $isActive);
    }

    public function setFrontendInput($frontendInput)
    {
        return $this->getData(FieldsInterface::FRONTEND_INPUT, $frontendInput);
    }

    public function setAttributeCode($attributeCode)
    {
        return $this->getData(FieldsInterface::ATTRIBUTE_CODE, $attributeCode);
    }

    public function setFrontendClass($frontendClass)
    {
        return $this->getData(FieldsInterface::FRONTEND_CLASS, $frontendClass);
    }

    public function setIsUsedInGrid($usedInGrid)
    {
        return $this->getData(FieldsInterface::IS_USED_IN_GRID, $usedInGrid);
    }

    public function setUsedInForms($usedInForms)
    {
        return $this->getData(FieldsInterface::USED_IN_FORMS, $usedInForms);
    }

    public function setCheckoutPosition($checkoutPosition)
    {
        return $this->getData(FieldsInterface::CHECKOUT_POSITION, $checkoutPosition);
    }

    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        $this->setData(FieldsInterface::IS_VISIBLE_ON_FRONT, $isVisibleOnFront);
    }

    public function setIsVisibleOnBack($isVisibleOnBack)
    {
        $this->setData(FieldsInterface::IS_VISIBLE_ON_BACK, $isVisibleOnBack);
    }

    public function setShowOnPdf($showOnPdf)
    {
        $this->setData(FieldsInterface::SHOW_ON_PDF, $showOnPdf);
    }

    public function setMaxLength($maxLength)
    {
        $this->setData(FieldsInterface::MAX_LENGTH, $maxLength);
    }

    public function setFrontendOption($frontendOption)
    {
        $this->setData(FieldsInterface::FRONTEND_OPTION, $frontendOption);
    }

    public function setShowLastValue($showLastValue)
    {
        $this->setData(FieldsInterface::SHOW_LAST_VALUE, $showLastValue);
    }

    public function setDisabledOnFrontend($disabledOnFrontend)
    {
        $this->setData(FieldsInterface::DISABLED_ON_FRONTEND, $disabledOnFrontend);
    }

    public function setCustomerGroups($customerGroups)
    {
        $this->setData(FieldsInterface::CUSTOMER_GROUPS, $customerGroups);
    }

    public function setTooltip($tooltip)
    {
        $this->setData(FieldsInterface::TOOLTIP, $tooltip);
    }

    public function setSaveSelected($saveSelected)
    {
        $this->setData(FieldsInterface::SAVE_SELECTED, $saveSelected);
    }

    public function setApplyDefault($applyDefault)
    {
        $this->setData(FieldsInterface::APPLY_DEFAULT, $applyDefault);
    }

    public function setAvailableOn($availableOn)
    {
        $this->setData(FieldsInterface::AVAILABLE_ON, $availableOn);
    }

    public function setAttributePosition($attributePosition)
    {
        return $this->setData(FieldsInterface::ATTRIBUTE_POSITION, $attributePosition);
    }

    public function setFieldRequired($fieldRequired)
    {
        return $this->setData(FieldsInterface::FIELD_REQUIRED, $fieldRequired);
    }

    // Helper functions

    /**
     * @return string
     */
    public function getGridUiComponent()
    {
        switch ($this->getFrontendInput()) {
            case InputType::SELECT:
            case InputType::MULTI_SELECT:
            case InputType::BOOLEAN:
                return 'Magento_Ui/js/grid/columns/select';
            case InputType::DATE:
            case InputType::DATETIME:
                return 'Magento_Ui/js/grid/columns/date';
            default:
                return 'Magento_Ui/js/grid/columns/column';
        }
    }

    public function getGridDataType()
    {
        switch ($this->getFrontendInput()) {
            case InputType::SELECT:
            case InputType::MULTI_SELECT:
            case InputType::BOOLEAN:
                return 'select';
            case InputType::DATE:
            case InputType::DATETIME:
                return 'date';
            default:
                return 'text';
        }
    }

    public function getGridUiFilter()
    {
        switch ($this->getFrontendInput()) {
            case InputType::SELECT:
            case InputType::MULTI_SELECT:
            case InputType::BOOLEAN:
                return 'select';
            case InputType::DATE:
            case InputType::DATETIME:
                return 'dateRange';
            default:
                return 'text';
        }
    }
}
