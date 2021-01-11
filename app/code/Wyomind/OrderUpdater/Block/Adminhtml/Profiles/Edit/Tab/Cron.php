<?php

namespace Wyomind\OrderUpdater\Block\Adminhtml\Profiles\Edit\Tab;

class Cron extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $module = "orderupdater";
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $formFactory, $data);
    }
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('profile');
        $form = $this->_formFactory->create();
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    public function getCronInterval()
    {
        return $this->framework->getStoreConfig($this->module . "/settings/cron_interval");
    }
    public function getCronSettings()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getCronSettings();
    }
    public function getTabLabel()
    {
        return __('Scheduled tasks');
    }
    public function getTabTitle()
    {
        return __('Scheduled tasks');
    }
    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }
}