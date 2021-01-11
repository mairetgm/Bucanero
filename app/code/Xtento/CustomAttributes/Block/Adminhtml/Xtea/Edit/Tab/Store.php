<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-10-07T13:34:53+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Xtea/Edit/Tab/Store.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;

/**
 * Class Store
 * @package Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab
 */
class Store extends Generic implements TabInterface
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * Body constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        SystemStore $systemStore,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->registry    = $registry;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareForm()
    {

        /** @var Fields $model */
        $model = $this->registry->registry('fields_data');

        /** @var Form $form */
        $form = $this->_formFactory->create();

//        $form->setHtmlIdPrefix('store_');

        $fieldSet = $form->addFieldset(
            'store_fieldset',
            ['legend' => __('Store')]
        );

        if ($model->getId()) {
            $fieldSet->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldSet->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'store_id',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, true),
                    'note' => __('In which store views will this attribute be visible in?')
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldSet->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $fieldSet->addField(
                'store_notice',
                'text',
                ['name' => 'store_notice', 'disabled' => true, 'label' => __('Store View'), 'title' => __('Store View')]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
            $model->setStoreNotice(__('Magento 2 Single Store Mode. No store view can/must be selected.'));
        }

        if ($model->getStoreId() == '') {
            $model->setStoreId(0);
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        parent::_prepareForm();

        return $this;
    }

    /**
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Store');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Store');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
