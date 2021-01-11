<?php

namespace Wyomind\OrderUpdater\Block\Adminhtml\Profiles;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    public $module = "OrderUpdater";
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Backend\Block\Widget\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Wyomind_OrderUpdater';
        $this->_controller = 'adminhtml_profiles';
        parent::_construct();
        $this->removeButton('save');
        $this->addButton('run', ['label' => __('Run Profile Now'), 'class' => 'save action-primary', 'onclick' => "jQuery('#run_i').val('1');  jQuery('#edit_form').submit();"]);
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $this->addButton('export', ['label' => __('Export'), 'class' => 'add', "onclick" => "setLocation('" . $this->getUrl('*/*/export', ['id' => $id]) . "')"]);
            $this->addButton('duplicate', ['label' => __('Duplicate'), 'class' => 'add ', 'onclick' => "jQuery('#id').remove(); jQuery('#back_i').val('1'); jQuery('#edit_form').submit();"]);
        }
        $this->addButton('save', ['label' => __('Save Profile'), 'class' => 'save', 'data_attribute' => ['mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']]]], -100);
        $this->updateButton('delete', 'label', __('Delete'));
    }
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('model')->getId()) {
            return __("Edit Profile '%1'", $this->escapeHtml($this->coreRegistry->registry('model')->getName()));
        } else {
            return __('New Profile');
        }
    }
    protected function _getSaveAndContinueUrl()
    {
        return "";
    }
}