<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Cron tab
 */
class Cron extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * Prepare form
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('profile');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('');
        $form->setValues($model->getData());
        $this->setForm($form);
        $this->setTemplate('profiles/edit/cron.phtml');
        return parent::_prepareForm();
    }
    /**
     * Return tab label
     * @return string
     */
    public function getTabLabel()
    {
        return __('Cron schedule');
    }
    /**
     * Return tab title
     * @return string
     */
    public function getTabTitle()
    {
        return __('Cron schedule');
    }
    /**
     * can show tab
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * Is visible
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
    public function getScheduledTask()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getScheduledTask();
    }
}