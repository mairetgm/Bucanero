<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-04-08T19:12:28+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Xtea/Edit/Tab/General.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\Sources\FieldType as EntityType;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class Main
 * @package Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var InputType
     */
    private $inputType;

    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * General constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param EntityType $entityType
     * @param InputType $inputType
     * @param Yesno $yesNo
     * @param SystemStore $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        EntityType $entityType,
        InputType $inputType,
        Yesno $yesNo,
        SystemStore $systemStore,
        array $data = []
    ) {
        $this->registry    = $registry;
        $this->entityType  = $entityType;
        $this->inputType   = $inputType;
        $this->yesNo       = $yesNo;
        $this->systemStore = $systemStore;

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

        if ($model->getData(FieldsInterface::FRONTEND_INPUT) == InputType::BOOLEAN) {
            $model->setData(FieldsInterface::FRONTEND_OPTION . '_yesno', $model->getData(FieldsInterface::FRONTEND_OPTION));
        }
        if ($model->getData(FieldsInterface::FRONTEND_INPUT) == InputType::SELECT) {
            $model->setData(FieldsInterface::FRONTEND_OPTION . '_radio_yesno', $model->getData(FieldsInterface::FRONTEND_OPTION));
        }
        if ($model->getData(FieldsInterface::FRONTEND_INPUT) == InputType::MULTI_SELECT) {
            $model->setData(FieldsInterface::FRONTEND_OPTION . '_multiplecheckbox_yesno', $model->getData(FieldsInterface::FRONTEND_OPTION));
        }

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $fieldSet = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Attribute Settings')]
        );

        $readOnly = '';
        if ($model->getId()) {
            $fieldSet->addField('entity_id', 'hidden', ['name' => 'entity_id']);
            $attributeObject = $this->registry->registry('custom_attribute_data');
            $labels = $attributeObject->getFrontendLabel();
            $label = is_array($labels) ? $labels[0] : $labels;
            $model->setData('attribute_label', $label);
            $readOnly = 'readonly';
        }

        $fieldSet->addField(
            'attribute_label',
            'text',
            [
                'name' => 'frontend_label[0]',
                'label' => __('Default Label'),
                'title' => __('Default label'),
                'required' => true,
                'note' => __('Label shown in checkout/admin. Translate in "Labels" tab.')
            ]
        );

        $types = $this->entityType->toOptionArray();

        /** The field type as customer/address/order */
        $fieldSet->addField(
            'type_id_visible',
            'select',
            [
                'name' => 'type_id_visible',
                'label' => __('Attribute Type'),
                'title' => __('Attribute Type'),
                'values' => $types,
                'required' => true,
            ]
        );

        $fieldSet->addField('type_id', 'hidden', ['name' => 'type_id']);

        $inputTypes = $this->inputType->getAvailable();

        $fieldSet->addField(
            FieldsInterface::FRONTEND_INPUT,
            'select',
            [
                'name' => FieldsInterface::FRONTEND_INPUT,
                'label' => __('Field Type'),
                'title' => __('Field Type'),
                'values' => $inputTypes,
                'required' => true,
                $readOnly => $readOnly
            ]
        );

        $fieldSet->addField(
            FieldsInterface::FRONTEND_OPTION . '_yesno',
            'select',
            [
                'name' => FieldsInterface::FRONTEND_OPTION . '_yesno',
                'label' => __('Show as checkbox'),
                'title' => __('Show as checkbox'),
                'note' => __('If enabled, instead of a yes/no dropdown, a checkbox will be shown.'),
                'values' => $this->yesNo->toArray()
            ]
        );
        $fieldSet->addField(
            FieldsInterface::FRONTEND_OPTION . '_radio_yesno',
            'select',
            [
                'name' => FieldsInterface::FRONTEND_OPTION . '_radio_yesno',
                'label' => __('Show as radio buttons'),
                'title' => __('Show as radio buttons'),
                'note' => __('If enabled, instead of a select, multiple radio buttons will be shown.'),
                'values' => $this->yesNo->toArray()
            ]
        );
        $fieldSet->addField(
            FieldsInterface::FRONTEND_OPTION . '_datetime_yesno',
            'select',
            [
                'name' => FieldsInterface::FRONTEND_OPTION . '_datetime_yesno',
                'label' => __('Show Date & Time'),
                'title' => __('Show Date & Time'),
                'note' => __('If disabled, just a date selector will be shown.'),
                'values' => $this->yesNo->toArray()
            ]
        );
        $fieldSet->addField(
            FieldsInterface::FRONTEND_OPTION . '_multiplecheckbox_yesno',
            'select',
            [
                'name' => FieldsInterface::FRONTEND_OPTION . '_multiplecheckbox_yesno',
                'label' => __('Show as checkboxes'),
                'title' => __('Show as checkboxes'),
                'note' => __('If enabled, instead of a multi select, multiple checkboxes will be shown.'),
                'values' => $this->yesNo->toArray()
            ]
        );

        $fieldSet->addField(
            FieldsInterface::IS_ACTIVE,
            'select',
            [
                'name' => FieldsInterface::IS_ACTIVE,
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'values' => $this->yesNo->toOptionArray(),
                'required' => true,
                'note' => __('If disabled, the field will not appear in backend/frontend.')
            ]
        );

        if ($model->getId()) {
            $form->getElement('frontend_input')->setDisabled(1);
        } else {
            $model->setData(FieldsInterface::IS_ACTIVE, true);
        }

        $form->getElement('type_id_visible')->setDisabled(1);

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
        return __('Attribute settings');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Attribute settings');
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
