<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-01-28T15:09:25+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Sales/OrderFields/Grid/Columns.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Sales\OrderFields\Grid;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\ResourceModel\Collection\CollectionFactory;
use Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create\OrderAttributes;
use Xtento\CustomAttributes\Helper\Data;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns as MagentoColumns;

/**
 * Class Columns
 */
class Columns
{
    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var UiComponentFactory
     */
    private $componentFactory;

    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @var Data
     */
    private $dataHelper;
    /**
     * @var int
     */
    protected $columnCount = 0;

    /**
     * Columns constructor.
     *
     * @param CollectionFactory $attributeCollectionFactory
     * @param UiComponentFactory $componentFactory
     * @param Attribute $attribute
     * @param Data $dataHelper
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory,
        UiComponentFactory $componentFactory,
        Attribute $attribute,
        Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->attribute = $attribute;
        $this->componentFactory = $componentFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @param MagentoColumns $subject
     * @param \Closure $result
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundPrepare(MagentoColumns $subject, \Closure $result)
    {
        if ($this->shouldAddAttributesToGrid($subject)) {
            $this->prepareCustomAttributes($subject);
        }

        $result();
    }

    /**
     * @param $context
     * @param Fields $attribute
     *
     * @return array
     */
    public function getColumnConfiguration($context, $attribute)
    {
        $attributeDataValue = $attribute->getData('attribute_data_values');
        $label = $attributeDataValue->getData('frontend_label');

        $filter = null;
        if ($attribute->getGridUiFilter()) {
            $filter = $attribute->getGridUiFilter();
        }

        $config = [
            'add_field' => false,
            'label' => $label,
            'visible' => true,
            'filter' => $filter,
            'dataType' => $attribute->getGridDataType(),
            'component' => $attribute->getGridUiComponent(),
            'sort_order' => $this->columnCount++
        ];

        /** @var Attribute $attribute */
        $options = $this->dataHelper->getAdminOptionValues(0, $attribute);
        if (!empty($options)) {
            $config['options'] = $options;
        }

        $columnConfiguration = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];

        return $columnConfiguration;
    }

    /**
     * @param $columnsComponent
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareCustomAttributes($columnsComponent)
    {
        $context = $columnsComponent->getContext();
        $components = $columnsComponent->getChildComponents();
        $attributeList = $this->getAttributesList();
        foreach ($attributeList as $attributes) {
            foreach ($attributes as $attribute) {
                $usedInGrid = $attribute[FieldsInterface::IS_USED_IN_GRID];
                $attributeCode = $attribute->getAttributeCode();

                if (!$usedInGrid) {
                    continue;
                }

                if (!isset($components[$attributeCode])) {
                    $column = $this->componentFactory->create(
                        $attributeCode,
                        'column',
                        $this->getColumnConfiguration($context, $attribute)
                    );
                    $column->prepare();
                    $columnsComponent->addComponent($attributeCode, $column);
                }
            }
        }
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributesList()
    {
        $dataHelper = $this->dataHelper;
        $fieldsList = $dataHelper->createFields([], OrderAttributes::ADMIN_GRID_LOCATION);

        return $fieldsList;
    }

    /**
     * @param MagentoColumns $columnsComponent
     *
     * @return bool
     */
    public function shouldAddAttributesToGrid($columnsComponent)
    {
        $componentId = $columnsComponent->getName();
        return $componentId == 'sales_order_columns';
    }
}

