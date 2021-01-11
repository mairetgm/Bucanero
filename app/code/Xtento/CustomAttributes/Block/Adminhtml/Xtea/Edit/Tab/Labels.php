<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Xtea/Edit/Tab/Labels.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

/**
 * Class Labels
 * @package Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab
 */
class Labels extends Template
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
        $this->_template = 'Magento_Catalog::catalog/product/attribute/labels.phtml';
    }

    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores());
        }
        return $this->_getData('stores');
    }

    public function getLabelValues()
    {
        $attribute = $this->getAttributeObject();

        if (!$attribute) {
            $labels = [[0 => '']];
            $storeLabels = $this->setData('label_values', $labels);
            return $storeLabels;
        }

        $values = (array)$this->getAttributeObject()->getLabel();
        $storeLabels = $this->getAttributeObject()->getStoreLabels();
        foreach ($this->getStores() as $store) {
            if ($store->getId() != 0) {
                $values[$store->getId()] = isset($storeLabels[$store->getId()]) ? $storeLabels[$store->getId()] : '';
            }
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
