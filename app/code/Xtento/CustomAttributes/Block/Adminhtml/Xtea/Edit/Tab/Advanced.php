<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-18T15:29:25+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Xtea/Edit/Tab/Advanced.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\Sources\FieldType as EntityType;
use Xtento\CustomAttributes\Model\Sources\InputType;
use IntlDateFormatter;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Helper\Data;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class Advanced
 * @package Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab
 */
class Advanced extends Generic implements TabInterface
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
     * Eav data
     *
     * @var Data
     */
    private $eavData;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        EntityType $entityType,
        InputType $inputType,
        Yesno $yesNo,
        SystemStore $systemStore,
        Data $eavData,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->entityType = $entityType;
        $this->inputType = $inputType;
        $this->yesNo = $yesNo;
        $this->systemStore = $systemStore;
        $this->eavData = $eavData;

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
        $attributeObject = $model;

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $fieldSet = $form->addFieldset(
            'advanced_fieldset',
            [
                'legend' => __('Advanced Settings'),
                'collapsable' => false
            ]
        );

        $attributeCode = FieldsInterface::ATTRIBUTE_CODE;
        if ($model->getId()) {
            $fieldSet->addField('entity_id', 'hidden', ['name' => 'entity_id']);
            $attributeCode = 'attribute_code_visible';
            $model->setData($attributeCode, $model->getAttributeCode());
        }

        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d',
            Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );

        $fieldSet->addField(
            $attributeCode,
            'text',
            [
                'name' => $attributeCode,
                'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'note' => __(
                    'This is used internally. It is the column name in the database, and used when updating the attribute via the API for example. Make sure you don\'t use spaces or more than %1 symbols.',
                    Attribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'class' => $validateClass,
                'required' => true
            ]
        );

        $validateClassMaxLength = sprintf(
            'maximum-length-%d',
            Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $fieldSet->addField(
            'max_length',
            'text',
            [
                'name' => 'max_length',
                'label' => __('Max Length'),
                'title' => __('Max Length'),
                'class' => $validateClassMaxLength,
                'note' => __('0 means no maximum length')
            ]
        );

        $fieldSet->addField(
            'default_value_text',
            'text',
            [
                'name' => 'default_value_text',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'values' => $attributeObject->getDefaultValue()
            ]
        );

        $fieldSet->addField(
            'default_value_yesno',
            'select',
            [
                'name' => 'default_value_yesno',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'values' => $this->yesNo->toArray()
            ]
        );

        $fieldSet->addField(
            FieldsInterface::SHOW_LAST_VALUE,
            'select',
            [
                'name' => FieldsInterface::SHOW_LAST_VALUE,
                'label' => __('Populate With Last Value'),
                'title' => __('Populate With Last Value'),
                'values' => $this->yesNo->toArray(),
                'note' => __('If enabled, the field will be pre-populated with the value the customer entered last time.')
            ]
        );

        $fieldSet->addField(
            'default_value_frontend_option_text',
            'text',
            [
                'name' => 'default_value_frontend_option_text',
                'label' => __('Default Value Frontend Option Text'),
                'title' => __('Default Value Frontend Option Text'),
                'values' => $attributeObject->getDefaultValue()
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(IntlDateFormatter::SHORT);
        $fieldSet->addField(
            'default_value_date',
            'date',
            [
                'name' => 'default_value_date',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'values' => $attributeObject->getDefaultValue(),
                'date_format' => $dateFormat
            ]
        );

        $fieldSet->addField(
            'default_value_textarea',
            'textarea',
            [
                'name' => 'default_value_textarea',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'values' => $attributeObject->getDefaultValue()
            ]
        );

        $eavDataValues = $this->eavData->getFrontendClasses($attributeObject->getEntityTypeId());

        $fieldSet->addField(
            FieldsInterface::FRONTEND_CLASS,
            'select',
            [
                'name' => FieldsInterface::FRONTEND_CLASS,
                'label' => __('Input Validation'),
                'title' => __('Input Validation'),
                'values' => $eavDataValues,
            ]
        );

        /*fieldSet->addField(
            FieldsInterface::ATTRIBUTE_POSITION,
            'text',
            [
                'name' => FieldsInterface::ATTRIBUTE_POSITION,
                'label' => __('Attribute Sort Order'),
                'title' => __('Attribute Sort Order'),
                'value' => 0,
                'required' => false,
                'note' => __('Used internally only. Use "Advanced" tab sort order to sort in checkout.')
            ]
        );*/

        if ($model->getId()) {
            $form->getElement('attribute_code_visible')->setDisabled(1);
            $fieldSet->addField(
                FieldsInterface::ATTRIBUTE_CODE,
                'hidden',
                [
                    'name' => FieldsInterface::ATTRIBUTE_CODE,
                    'value' => $model->getAttributeCode()
                ]
            );
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

    protected function _afterToHtml($html)
    {
        $jsScripts = $this->getLayout()->createBlock('Magento\Eav\Block\Adminhtml\Attribute\Edit\Js')->toHtml();
        return $html . $jsScripts;
    }
}
