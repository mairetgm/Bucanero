<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/AddButton.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml;

use Xtento\CustomAttributes\Model\Sources\FieldType as EntityType;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Customer\Model\Customer;

/**
 * Class AddButton
 * @package Xtento\CustomAttributes\Block\Adminhtml
 */
class AddButton extends Container
{
    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * AddButton constructor.
     * @param Context $context
     * @param EntityType $entityType
     * @param array $data
     */
    public function __construct(
        Context $context,
        EntityType $entityType,
        array $data = []
    ) {
        $this->entityType = $entityType;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_template',
            'label' => __('Add Attribute'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->entityOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    public function entityOptions()
    {
        $splitButtonOptions = [];
        $types = $this->entityType->getAvailable();

        foreach ($types as $typeId => $type) {
            $splitButtonOptions[$typeId] = [
                'label' => $type,
                'onclick' => "setLocation('" . $this->entityUrl($typeId) . "')",
                'default' => 'order_field' == $typeId,
            ];
        }

        return $splitButtonOptions;
    }

    /**
     * @param $typeId
     * @return string
     */
    private function entityUrl($typeId)
    {
        return $this->getUrl(
            '*/*/newentity',
            ['type' => $typeId]
        );
    }
}
