<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Block\Adminhtml\Profiles\Edit\Tab;

/**
 * Filter tab
 */
class Filters extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $registry, $formFactory, $data);
    }
    public function getOrderConfig()
    {
        return $this->_orderConfig;
    }
    public function getCustomerGroup()
    {
        return $this->_customerGroup;
    }
    public function getHelper()
    {
        return $this->_helper;
    }
    public function getResource()
    {
        return $this->_resource;
    }
    public function getCoreHelper()
    {
        return $this->_framework;
    }
    /**
     * Prepare form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('profile');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('');
        $this->setForm($form);
        //        $fieldset = $form->addFieldset('ordersexporttool_profiles_edit_product_types', ['legend' => __('Products')]);
        //
        //        $fieldset->addField(
        //            'product_type',
        //            'checkboxes',
        //            [
        //                'label' => 'Product types ',
        //                'name' => 'product_type[]',
        //                'values' => [
        //                    [
        //                        'value' => 'simple',
        //                        'label' => 'Simple, Virtual, Downloadable products'
        //                    ],
        //                    [
        //                        'value' => 'configurable',
        //                        'label' => 'Configurable products'
        //                    ],
        //                    [
        //                        'value' => 'grouped_parent',
        //                        'label' => 'Grouped products'
        //                    ],
        //                    [
        //                        'value' => 'bundle_parent',
        //                        'label' => 'Bundle products (main product)'
        //                    ],
        //                    [
        //                        'value' => 'bundle_children',
        //                        'label' => '<span style="color: #666666;font-style: italic;">Children of bundle products</span>'
        //                    ]
        //                ],
        //                'onchange' => '',
        //                'disabled' => false,
        //                "note" => "<br/><br/><b>" . __("Which product type should be included in the export file(s). Note that it's useless if you only export the order data and not the purchased items.") . "</b>"
        //            ]
        //        );
        $fieldset = $form->addFieldset('ordersexporttool_profiles_edit_settings', ['legend' => __('Store views')]);
        $fieldset->addField('store_id', 'multiselect', ['label' => __('Export from Store View'), 'title' => __('Export from Store View'), 'name' => 'store_id', 'class' => 'required-entry', 'required' => true, 'values' => $this->systemStore->getStoreValuesForForm(false, false), "note" => "<b>" . __("From which store views should the orders be exported. Select at least one.") . "</b>"]);
        $form->setValues($model->getData());
        $this->setTemplate('profiles/edit/filters.phtml');
        return parent::_prepareForm();
    }
    /**
     * Return tab label
     * @return string
     */
    public function getTabLabel()
    {
        return __('Filters');
    }
    /**
     * Return tab title
     * @return string
     */
    public function getTabTitle()
    {
        return __('Filters');
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
    /**
     * get customer groups
     * @return string
     */
    public function getCustomerGroups()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getCustomerGroups();
    }
    /**
     * get order states
     * @return string
     */
    public function getStates()
    {
        $model = $this->_coreRegistry->registry('profile');
        return $model->getStates();
    }
}