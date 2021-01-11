<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-04-20T10:20:33+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Eav/Model/AbstractAttributePlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Eav\Model;

use Magento\Store\Model\StoreManagerInterface;
use Xtento\CustomAttributes\Block\Customer\Form\Register;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Magento\Framework\Api\FilterBuilder;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;

class AbstractAttributePlugin
{
    protected static $allCustomAttributes = false;
    protected static $customAttributes = [];
    protected static $dontRun = false;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AttributePlugin constructor.
     *
     * @param DataHelper $dataHelper
     * @param FilterBuilder $filterBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DataHelper $dataHelper,
        FilterBuilder $filterBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->dataHelper = $dataHelper;
        $this->filterBuilder = $filterBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $subject
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function checkAttributes($subject)
    {
        if (self::$dontRun) {
            return false;
        }
        self::$dontRun = true;

        $attributeFound = false;

        // Load attributes
        $customAttributesByEntity = $this->fetchCustomAttributes($subject->getEntityTypeId());
        foreach ($customAttributesByEntity as $entity => $customAttributes) {
            foreach ($customAttributes as $attributeCode => $customAttribute) {
                if ($customAttribute->getAttributeCode() == $subject->getAttributeCode()) {
                    $attributeFound = true;
                    $storeIds = $customAttribute->getStoreId();
                    if (!is_array($storeIds)) {
                        $storeIds = explode(",", $storeIds);
                    }
                    $applyToAllStoreViews = false;
                    foreach ($storeIds as $storeId) {
                        if ($storeId == 0) {
                            $applyToAllStoreViews = true;
                            break 1;
                        }
                    }
                    $currentStoreId = $this->storeManager->getStore()->getId();
                    if (!$applyToAllStoreViews && !in_array($currentStoreId, $storeIds)) {
                        // Attribute not enabled for current store, not required thus
                        $subject->setData('scope_is_required', 0);
                    }
                    break 2;
                }
            }
        }

        if (!$attributeFound) {
            // Check is one of our attributes, otherwise say it was found
            if (self::$allCustomAttributes === false) {
                self::$allCustomAttributes = $this->dataHelper->createFields([], Register::CUSTOMER_LOCATION, null, false, true);
            }
            $isOurAttribute = false;
            foreach (self::$allCustomAttributes as $entity => $customAttributes) {
                foreach ($customAttributes as $attributeCode => $customAttribute) {
                    if ($customAttribute->getAttributeCode() == $subject->getAttributeCode()) {
                        $isOurAttribute = true;
                    }
                }
            }
            if (!$isOurAttribute) {
                $attributeFound = true;
            }
        }

        self::$dontRun = false;
        return $attributeFound;
    }

    /**
     * @return mixed
     */
    protected function fetchCustomAttributes($attributeEntityTypeId)
    {
        if (array_key_exists($attributeEntityTypeId, self::$customAttributes)) {
            return self::$customAttributes[$attributeEntityTypeId];
        }

        $attributeType = $this->dataHelper->getEntityTypeById($attributeEntityTypeId);
        $attributeTypeCode = isset($attributeType['entity_type_code']) ? $attributeType['entity_type_code'] : false;

        if ($attributeTypeCode === false) {
            return [];
        }

        $filters = [
            $this->filterBuilder
                ->setField(FieldsInterface::ATTRIBUTE_TYPE)
                ->setValue($attributeTypeCode)
                ->create()
        ];

        self::$customAttributes[$attributeEntityTypeId] = $this->dataHelper->createFields($filters, Register::CUSTOMER_LOCATION, null, false, true);
        return self::$customAttributes[$attributeEntityTypeId];
    }
}