<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-12-11T15:22:59+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Order/View/Attributes.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Order\View;

use Magento\Eav\Model\Attribute;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use \Magento\Framework\Registry;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Model\FieldProcessor\AddValue;

class Attributes extends \Magento\Sales\Block\Order\Info
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
     * Attributes constructor.
     *
     * @param TemplateContext $context
     * @param Registry $registry
     * @param PaymentHelper $paymentHelper
     * @param AddressRenderer $addressRenderer
     * @param DataHelper $dataHelper
     * @param AddValue $addValue
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        PaymentHelper $paymentHelper,
        AddressRenderer $addressRenderer,
        DataHelper $dataHelper,
        AddValue $addValue,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $paymentHelper, $addressRenderer, $data);
        $this->dataHelper = $dataHelper;
        $this->addValue = $addValue;
    }

    /**
     * @return array|bool
     */
    public function fields()
    {
        if (!$this->dataHelper->getModuleHelper()->confirmEnabled(true) || !$this->dataHelper->getModuleHelper()->isModuleEnabled()) {
            return [];
        }

        $fields = $this->dataHelper->createFields([], Data::ORDER_VIEW);

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
