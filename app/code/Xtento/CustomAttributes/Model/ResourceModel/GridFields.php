<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:04+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/ResourceModel/GridFields.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\ResourceModel;

use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create\OrderAttributes;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sales\Model\ResourceModel\Grid;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProviderInterface;

class GridFields extends Grid
{
    /**
     * GridFields constructor.
     *
     * @param Data $dataHelper
     * @param Context $context
     * @param string $mainTableName
     * @param string $gridTableName
     * @param string $orderIdField
     * @param array $joins
     * @param array $columns
     * @param string $connectionName
     * @param NotSyncedDataProviderInterface $notSyncedDataProvider
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        $mainTableName,
        $gridTableName,
        $orderIdField,
        array $joins = [],
        array $columns = [],
        $connectionName = null,
        NotSyncedDataProviderInterface $notSyncedDataProvider = null
    ) {
        // Dynamically inject additional fields into the columns argument
        $fieldsList = $dataHelper->createFields();

        $columnValues = [];
        foreach ($fieldsList as $fieldData) {
            foreach ($fieldData as $fields) {
                $attributeCode = $fields['attribute_code'];
                $columnValues[$attributeCode] = 'sales_order.' . $attributeCode;
            }
        }

        $initialValues = $columns;
        $finalValues = array_merge($initialValues, $columnValues);
        $columns = $finalValues;

        parent::__construct($context, $mainTableName, $gridTableName, $orderIdField, $joins, $columns, $connectionName, $notSyncedDataProvider);
    }
}
