<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-04-17T12:31:55+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Xtea/Edit/Tab/FrontEnd.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\Sources\AttributeValidation;
use Xtento\CustomAttributes\Model\Sources\FieldRequired;
use Xtento\CustomAttributes\Model\Sources\FieldType as EntityType;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Xtento\CustomAttributes\Model\Sources\IsVisibleOnFront;
use Xtento\CustomAttributes\Model\Sources\ShowOn;
use Xtento\CustomAttributes\Model\Sources\ShowOnAddress;
use Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Xtento\CustomAttributes\Model\Sources\UsedInForms;

/**
 * Class Main
 * @package Xtento\CustomAttributes\Block\Adminhtml\Xtea\Edit\Tab
 */
class FrontEnd extends Generic implements TabInterface
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
     * @var IsVisibleOnFront
     */
    private $isVisibleOnFront;

    /**
     * @var ShowOnAddress
     */
    private $showOnAddress;

    /**
     * @var CustomerGroupsOptionsProvider
     */
    private $customerGroupsOptionsProvider;

    /**
     * @var UsedInForms
     */
    private $usedInForms;

    /**
     * @var FieldRequired
     */
    private $fieldRequired;

    /**
     * FrontEnd constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param EntityType $entityType
     * @param InputType $inputType
     * @param Yesno $yesNo
     * @param SystemStore $systemStore
     * @param IsVisibleOnFront $isVisibleOnFront
     * @param ShowOnAddress $showOnAddress
     * @param CustomerGroupsOptionsProvider $customerGroupsOptionsProvider
     * @param FieldRequired $fieldRequired
     * @param UsedInForms $usedInForms
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
        IsVisibleOnFront $isVisibleOnFront,
        ShowOnAddress $showOnAddress,
        CustomerGroupsOptionsProvider $customerGroupsOptionsProvider,
        FieldRequired $fieldRequired,
        UsedInForms $usedInForms,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->entityType = $entityType;
        $this->inputType = $inputType;
        $this->yesNo = $yesNo;
        $this->systemStore = $systemStore;
        $this->isVisibleOnFront = $isVisibleOnFront;
        $this->showOnAddress = $showOnAddress;
        $this->customerGroupsOptionsProvider = $customerGroupsOptionsProvider;
        $this->fieldRequired = $fieldRequired;
        $this->usedInForms = $usedInForms;

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

        $yesno = $this->yesNo->toOptionArray();

        // Admin settings
        $fieldSet = $form->addFieldset(
            'admin_fieldset',
            ['legend' => __('Admin Settings')]
        );

        $fieldSet->addField(
            FieldsInterface::IS_VISIBLE_ON_BACK,
            'select',
            [
                'name' => FieldsInterface::IS_VISIBLE_ON_BACK,
                'label' => __('Visible in Backend'),
                'title' => __('Visible in Backend'),
                'values' => $yesno,
                'required' => false,
                'note' => __('Field should be shown when viewing an order, etc.')
            ]
        );
        if (is_null($model->getData(FieldsInterface::IS_VISIBLE_ON_BACK))) {
            $model->setData(FieldsInterface::IS_VISIBLE_ON_BACK, 1);
        }

        if ($model->getAttributeTypeId() == CustomAttributes::ORDER_ENTITY) {
            $fieldSet->addField(
                FieldsInterface::IS_USED_IN_GRID,
                'select',
                [
                    'name' => FieldsInterface::IS_USED_IN_GRID,
                    'label' => __('Show in admin grids'),
                    'title' => __('Show in admin grids'),
                    'values' => $yesno,
                    'note' => __('Select "Yes" to add this attribute to the list of column options in the "Sales > Orders" grid.'),
                ]
            );
            if (is_null($model->getData(FieldsInterface::IS_USED_IN_GRID))) {
                $model->setData(FieldsInterface::IS_USED_IN_GRID, 1);
            }
        }

        $fieldSet->addField(
            FieldsInterface::SHOW_ON_PDF,
            'select',
            [
                'name' => FieldsInterface::SHOW_ON_PDF,
                'label' => __('Show on PDF'),
                'title' => __('Show on PDF'),
                'values' => $yesno,
                'required' => false,
            ]
        );

        // Frontend Settings
        $fieldSet = $form->addFieldset(
            'checkout_fieldset',
            ['legend' => __('Display Settings')]
        );

        if ($model->getId()) {
            $fieldSet->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }

        $types = $this->fieldRequired->getAvailable();
        $fieldSet->addField(
            FieldsInterface::FIELD_REQUIRED,
            'select',
            [
                'name' => FieldsInterface::FIELD_REQUIRED,
                'label' => __('Required'),
                'title' => __('Required'),
                'values' => $types,
                'required' => false,
            ]
        );

        $fieldSet->addField(
            FieldsInterface::CHECKOUT_POSITION,
            'text',
            [
                'name' => FieldsInterface::CHECKOUT_POSITION,
                'label' => __('Checkout Field Position'),
                'title' => __('Checkout Field Position'),
                'required' => false,
                'note' => __('Leave blank for default checkout position.'),

            ]
        );

        $types = $this->entityType->toOptionArray();

        $onlyType = [];
        if ($type = $model->getData('type_id')) {
            $onlyType[] = $types[$type];
        }

        $types = $this->isVisibleOnFront->getAvailable();

        if ($model->getAttributeTypeId() === CustomAttributes::ORDER_ENTITY) {
            unset($types[3]);
            unset($types[4]);
        } else {
            unset($types[2]);
        }

        $fieldSet->addField(
            FieldsInterface::IS_VISIBLE_ON_FRONT,
            'multiselect',
            [
                'name' => FieldsInterface::IS_VISIBLE_ON_FRONT,
                'label' => __('Visible on frontend'),
                'title' => __('Visible on frontend'),
                'values' => $types,
                'required' => false,
                'style' => 'height: 130px'
            ]
        );

        if ($model->getAttributeTypeId() === CustomAttributes::CUSTOMER_ENTITY) {
            $fieldSet->addField(
                FieldsInterface::DISABLED_ON_FRONTEND,
                'select',
                [
                    'name' => FieldsInterface::DISABLED_ON_FRONTEND,
                    'label' => __('Disabled on frontend'),
                    'title' => __('Disabled on frontend'),
                    'values' => $yesno,
                    'required' => false,
                    'note' => __('If enabled, this attribute is visible on the frontend, but cannot be edited.')
                ]
            );
        }

        if ($model->getAttributeTypeId() !== CustomAttributes::ORDER_ENTITY) {
            $types = $this->usedInForms->getAvailable($model->getAttributeTypeId());
            $fieldSet->addField(
                FieldsInterface::USED_IN_FORMS,
                'multiselect',
                [
                    'name' => FieldsInterface::USED_IN_FORMS,
                    'label' => __('Visible input areas'),
                    'title' => __('Visible input areas'),
                    'values' => $types,
                    'required' => false,
                    'note' => __('Where do you want this attribute to be inputtable/editable exactly?'),

                ]
            );
            if (!$model->getId()) {
                $model->setData(FieldsInterface::USED_IN_FORMS, array_column($types, 'value'));
            }
        }

//        $fieldSet->addField(
//            FieldsInterface::SAVE_SELECTED,
//            'select',
//            [
//                'name' => FieldsInterface::SAVE_SELECTED,
//                'label' => __('Save Entered Value For Future Checkout'),
//                'title' => __('Save Entered Value For Future Checkout'),
//                'values' => $yesno,
//                'note' => __('If set to "Yes", previously entered value will be used during checkout. Works for registered customers only.'),
//            ]
//        );

//        $fieldSet->addField(
//            FieldsInterface::APPLY_DEFAULT,
//            'select',
//            [
//                'name' => FieldsInterface::APPLY_DEFAULT,
//                'label' => __('Automatically Apply Default Value'),
//                'title' => __('Automatically Apply Default Value'),
//                'values' => $yesno,
//                'note' => __(
//                    'If set to `Yes`,
//                     the default value will be automatically applied for each order if
//                     attribute value is not entered or not visible at the frontend.'
//                ),
//            ]
//        );

        $types = $this->showOnAddress->getAvailable();

        if ($model->getAttributeTypeId() === Customer::ENTITY ||
            $model->getAttributeTypeId() === CustomAttributes::ADDRESS_ENTITY
        ) {
            $types = $this->showOnAddress->getAvailableNotOrder();
        }

        if ($model->getAttributeTypeId() === CustomAttributes::ADDRESS_ENTITY
        ) {
            $fieldSet->addField(
                FieldsInterface::AVAILABLE_ON,
                'select',
                [
                    'name' => FieldsInterface::AVAILABLE_ON,
                    'label' => __('Show For Address Type(s)'),
                    'title' => __('Show For Address Type(s)'),
                    'values' => $types,
                    'required' => false,
                ]
            );
        } else {
            $fieldSet->addField(
                FieldsInterface::AVAILABLE_ON,
                'select',
                [
                    'name' => FieldsInterface::AVAILABLE_ON,
                    'label' => __('Checkout Location'),
                    'title' => __('Checkout Location'),
                    'values' => $types,
                    'required' => false,
                ]
            );
        }

        $fieldSet->addField(
            FieldsInterface::TOOLTIP,
            'text',
            [
                'name' => FieldsInterface::TOOLTIP,
                'label' => __('Checkout Tooltip'),
                'title' => __('Checkout Tooltip'),
                'required' => false,
                'note' => __('This is help text for customers that is displayed on checkout page'),

            ]
        );

        $customerGroups = $this->customerGroupsOptionsProvider->toOptionArray();
        array_unshift($customerGroups, ['value' => '', 'label' => __('--- All Customer Groups ---')]);
        $fieldSet->addField(
            FieldsInterface::CUSTOMER_GROUPS,
            'multiselect',
            [
                'name' => FieldsInterface::CUSTOMER_GROUPS,
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'values' => $customerGroups,
            ]
        );

        if (!$model->getId()) {
            $model->setData(FieldsInterface::AVAILABLE_ON, Data::AVAILABLE_HIDDEN);
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
        return __('Display Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Display Settings');
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
