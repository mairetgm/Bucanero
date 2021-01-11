<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Xtea/Edit.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Xtea;

use Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Buttons\DeleteButton;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Xtento\CustomAttributes\Block\Adminhtml\Xtea
 */
class Edit extends Container
{

    private $deleteButton;

    private $coreRegistry;

    public function __construct(
        Context $context,
        Registry $registry,
        DeleteButton $deleteButton,
        array $data = []
    ) {
        $this->coreRegistry    = $registry;
        $this->deleteButton    = $deleteButton;

        parent::__construct($context, $data);
    }

    /**
     *
     * @return void
     */
    public function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Xtento_CustomAttributes';
        $this->_controller = 'adminhtml_xtea';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));

        $this->buttonList->add(
            'saveanddelte',
            $this->deleteButton->getButtonData()
        );

        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );
    }
}
