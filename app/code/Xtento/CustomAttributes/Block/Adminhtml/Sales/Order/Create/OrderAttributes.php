<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-05-18T10:30:56+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Sales/Order/Create/OrderAttributes.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Model\FieldProcessor\AddValue;
use Xtento\CustomAttributes\Model\Fields;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Magento\Eav\Model\Attribute;
use Magento\Framework\Api\FilterBuilder;

class OrderAttributes extends \Magento\Backend\Block\Widget\Form\Generic
{
    const ADMIN_ORDER_LOCATION = 'admin_order_location';
    const ADMIN_GRID_LOCATION = 'admin_grid_location';

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var AddValue
     */
    private $addValue;

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    /**
     * @var
     */
    protected $_fields;

    /**
     * OrderAttributes constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param DataHelper $dataHelper
     * @param FilterBuilder $filterBuilder
     * @param AddValue $addValue
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        DataHelper $dataHelper,
        FilterBuilder $filterBuilder,
        AddValue $addValue,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->filterBuilder = $filterBuilder;
        $this->addValue = $addValue;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _prepareForm()
    {
        $this->_form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ]
            ]
        );
        $this->_form->setUseContainer(true);

        $fieldset = $this->_form->addFieldset(
            'attribute_fieldset',
            ['legend' => '', 'collapsable' => false]
        );

        $this->_fields = $this->fields();

        if (empty($this->_fields)) {
            return $this;
        }

        /** @var Fields $field */
        foreach ($this->_fields as $field) {
            /** @var Attribute $attribute */
            $attribute = $field->getData(DataHelper::ATTRIBUTE_DATA);

            $attributeOptions = $attribute->getOptions();

            $options = [];
            if (!empty($attributeOptions)) {
                foreach ($attributeOptions as $attributeOption) {
                    $options[$attributeOption->getValue()] = $attributeOption->getLabel();
                }
            }

            $inputType = $field->getFrontendInput();

            if ($inputType === InputType::BOOLEAN) {
                $inputType = InputType::SELECT;
                $boolOptions = $this->dataHelper->yesNoCheckout();

                foreach ($boolOptions as $boolOption) {
                    $options[$boolOption['value']] = $boolOption['label'];
                }
            }

            $values = [];
            if ($inputType === InputType::MULTI_SELECT) {
                $options = [];
                foreach ($attributeOptions as $attributeOption) {
                    $values[] = [
                        'label' => $attributeOption->getLabel(),
                        'value' => $attributeOption->getValue()
                    ];
                }
            }

            $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            $element = $fieldset->addField(
                $attribute->getAttributeCode(),
                $inputType,
                [
                    'name' => 'order[' . CustomAttributes::ORDER_ENTITY . '][' . $attribute->getAttributeCode() . ']',
                    'label' => $attribute->getData('frontend_label'),
                    'title' => $attribute->getBackendLabel(),
                    'value' => $attribute->getDefaultValue(),
                    'values' => $values,
                    'options' => $options,
                    'required' => $field->getFieldRequired() == 1,
                    'date_format' => $dateFormat,
//                    'class' => $validateClass
                ]
            );
        }

        $this->_form->setValues($this->getFormValues());
        $this->setForm($this->_form);
        return $this;
    }

    /**
     * @return $this
     */
    protected function _initFormValues()
    {
        parent::_initFormValues();
        if (!$this->_coreRegistry->registry('current_order')) {
            return $this;
        }

        foreach ($this->_fields as $field) {
            $attribute = $field->getData(DataHelper::ATTRIBUTE_DATA);
            $element = $this->getForm()->getElement($attribute->getAttributeCode());

            $value = $attribute->getDefaultValue();
            if ($order = $this->_coreRegistry->registry('current_order')) {
                // Get value for current order
                $result = $this->addValue->addValues($attribute, $order, true);
                $value = $result->getData('value') . $result->getData('billing_value') . $result->getData('shipping_value');
                // Special values for checkbox, select etc.
                $inputType = $field->getFrontendInput();
                if ($inputType === InputType::BOOLEAN || $inputType === InputType::MULTI_SELECT) {
                    $value = $order->getData($attribute->getAttributeCode());
                }
            }
            $element->setValue($value);
        }

        return $this;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function fields()
    {
        $filters = [
            $this->filterBuilder
                ->setField(FieldsInterface::ATTRIBUTE_TYPE)
                ->setValue(CustomAttributes::ORDER_ENTITY)
                ->create()
        ];

        $fields = $this->dataHelper->createFields($filters, self::ADMIN_ORDER_LOCATION);

        if (empty($fields[CustomAttributes::ORDER_ENTITY])) {
            return [];
        }

        return $fields[CustomAttributes::ORDER_ENTITY];
    }

    public function getFormValues()
    {
        $data = [];
        $fields = $this->fields();
        /** @var Fields $field */
        foreach ($fields as $fieldKey => $field) {
            /** @var Attribute $attribute */
            $attribute = $field->getData(DataHelper::ATTRIBUTE_DATA);
            $data[$fieldKey] = $attribute->getDefaultValue();
        }

        return $data;
    }
}
