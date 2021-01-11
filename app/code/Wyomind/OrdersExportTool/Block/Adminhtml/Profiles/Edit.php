<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Wyomind_OrdersExportTool';
        $this->_controller = 'adminhtml_profiles';
        parent::_construct();
        $this->removeButton('save');
        $this->removeButton('reset');
        $this->updateButton('delete', 'label', __('Delete'));
        if ($this->getRequest()->getParam('id')) {
            $this->addButton('duplicate', ['label' => __('Duplicate'), 'class' => 'add', 'onclick' => "jQuery('#id').remove(); jQuery('#back_i').val('1'); jQuery('#edit_form').submit();"]);
            $this->addButton('generate', ['label' => __('Generate'), 'class' => 'save', 'onclick' => "require(['oet_index'], function (index) { index.generateFromEdit(); })"]);
        }
        $this->addButton('save', ['label' => __('Save'), 'class' => 'save', 'onclick' => "jQuery('#back_i').val('1'); jQuery('#edit_form').submit();"]);
    }
}