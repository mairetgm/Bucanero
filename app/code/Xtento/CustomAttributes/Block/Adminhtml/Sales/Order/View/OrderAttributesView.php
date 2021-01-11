<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-02-13T21:40:17+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Sales/Order/View/OrderAttributesView.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\View;

use Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create\OrderAttributes;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Model\FieldProcessor\AddValue;
use Magento\Backend\Block\Template\Context;
use Magento\Eav\Model\Attribute;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Admin;

class OrderAttributesView extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var AddValue
     */
    private $addValue;

    /**
     * OrderAttributesView constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param DataHelper $dataHelper
     * @param AddValue $addValue
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        DataHelper $dataHelper,
        AddValue $addValue,
        array $data = []
    ) {
        $this->dataHelper        = $dataHelper;
        $this->addValue          = $addValue;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * @return array|bool
     */
    public function fields()
    {
        if (!$this->dataHelper->getModuleHelper()->confirmEnabled(true) || !$this->dataHelper->getModuleHelper()->isModuleEnabled()) {
            return [];
        }

        $fields = $this->dataHelper->createFields([], OrderAttributes::ADMIN_ORDER_LOCATION);

        if (empty($fields)) {
            return [];
        }

        return $fields;
    }

    /**
     * @param Attribute $attribute
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addValues($attribute)
    {
        $order    = $this->getOrder();

        $addValue = $this->addValue;
        $result   = $addValue->addValues($attribute, $order);

        return $result;
    }
}
