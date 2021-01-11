<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-03-27T20:28:24+00:00
 * File:          app/code/Xtento/CustomAttributes/Api/Data/FieldsInterface.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Api\Data;

interface FieldsInterface
{
    const ENTITY_ID = 'entity_id';
    const ATTRIBUTE_ID = 'attribute_id';
    const ATTRIBUTE_TYPE = 'type_id';
    const STORE_ID = 'store_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const IS_ACTIVE = 'is_active';
    const FRONTEND_INPUT = 'frontend_input';
    const ATTRIBUTE_CODE = 'attribute_code';
    const FRONTEND_CLASS = 'frontend_class';
    const IS_USED_IN_GRID = 'is_used_in_grid';
    const USED_IN_FORMS = 'used_in_forms';
    const CHECKOUT_POSITION = 'checkout_position';
    const IS_VISIBLE_ON_FRONT= 'is_visible_on_front';
    const IS_VISIBLE_ON_BACK = 'is_visible_on_back';
    const SHOW_ON_PDF = 'show_on_pdf';
    const MAX_LENGTH = 'max_length';
    const FRONTEND_OPTION = 'frontend_option';
    const SHOW_LAST_VALUE = 'show_last_value';
    const DISABLED_ON_FRONTEND = 'disabled_on_frontend';
    const CUSTOMER_GROUPS = 'customer_groups';
    const TOOLTIP = 'tooltip';
    const SAVE_SELECTED = 'save_selected';
    const APPLY_DEFAULT = 'apply_default';
    const AVAILABLE_ON = 'available';
    const ATTRIBUTE_POSITION = 'attribute_position';
    const FIELD_REQUIRED = 'field_required';

    public function getId();

    public function getAttributeId();

    public function getAttributeTypeId();

    public function getStoreId();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getIsActive();

    public function getFrontendInput();

    public function getAttributeCode();

    public function getFrontendClass();

    public function getIsUsedInGrid();

    public function getUsedInForms();

    public function getCheckoutPosition();

    public function getIsVisibleOnFront();

    public function getIsVisibleOnBack();

    public function getShowOnPdf();

    public function getMaxLength();

    public function getFrontendOption();

    public function getShowLastValue();

    public function getDisabledOnFrontend();

    public function getCustomerGroups();

    public function getTooltip();

    public function getSaveSelected();

    public function getApplyDefault();

    public function getAvailableOn();

    public function getAttributePosition();

    public function getFieldRequired();

    public function setId($entityId);

    public function setAttributeId($attributeId);

    public function setAttributeTypeId($attributeTypeId);

    public function setStoreId($storeId);

    public function setCreatedAt($createdAt);

    public function setUpdatedAt($updatedAt);

    public function setIsActive($isActive);

    public function setFrontendInput($frontendInput);

    public function setAttributeCode($attributeCode);

    public function setFrontendClass($frontendClass);

    public function setIsUsedInGrid($usedInGrid);

    public function setUsedInForms($usedInForms);

    public function setCheckoutPosition($checkoutPosition);

    public function setIsVisibleOnFront($isVisibleOnFront);

    public function setIsVisibleOnBack($isVisibleOnBack);

    public function setShowOnPdf($showOnPdf);

    public function setMaxLength($maxLength);

    public function setFrontendOption($frontendOption);

    public function setShowLastValue($showLastValue);

    public function setDisabledOnFrontend($disabledOnFrontend);

    public function setCustomerGroups($customerGroups);

    public function setTooltip($tooltip);

    public function setSaveSelected($saveSelected);

    public function setApplyDefault($applyDefault);

    public function setAvailableOn($availableOn);

    public function setAttributePosition($attributePosition);

    public function setFieldRequired($fieldRequired);
}
