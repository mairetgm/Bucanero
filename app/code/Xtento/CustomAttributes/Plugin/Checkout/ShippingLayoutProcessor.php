<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-09-17T19:14:29+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Checkout/ShippingLayoutProcessor.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Checkout;

use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Model\Sources\ShowOnAddress;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Customer\Model\Customer;

/**
 * Class ShippingLayoutProcessor
 * @package Xtento\CustomAttributes\Plugin\Checkout
 */
class ShippingLayoutProcessor
{
    const SHIPPING_VALIDATORS = 'customer-order-custom-shipping-validator';

    /**
     * @var []
     */
    private $result;

    /**
     * @var DataHelper
     */
    private $data;

    /**
     * @var ShowOnAddress
     */
    private $showOnAddress;

    /**
     * ShippingLayoutProcessor constructor.
     * @param DataHelper $data
     */
    public function __construct(
        DataHelper $data,
        ShowOnAddress $showOnAddress
    ) {
        $this->data          = $data;
        $this->showOnAddress = $showOnAddress;
    }

    /**
     * @param LayoutProcessor $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    //@codingStandardsIgnoreLine
    public function afterProcess(
        LayoutProcessor $subject,
        array $result
    ) {
        $this->result = $result;
        if ($this->data->getModuleHelper()->isModuleEnabled()) {
            $this->iterator();
        }
        return $this->result;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function iterator()
    {
        $fieldHelperData = $this->data->createFields();

        foreach ($fieldHelperData as $fields => $types) {
            if ($fields === CustomAttributes::ADDRESS_ENTITY) {
                foreach ($types as $fieldId => $data) {
                    if (!$this->displayFieldInCheckout($data)) {
                        continue;
                    }

                    $fieldValues = $data[DataHelper::FIELD_VALUES];
                    if (!is_array($fieldValues)) {
                        continue;
                    }

                    if ($fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_BOTH ||
                        $fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_SHIPPING
                    ) {
                        $specialFields = 'ea' . CustomAttributes::ADDRESS_ENTITY . 'ea_' . $fieldId;
                        $data['dataScope'] = 'shippingAddress.custom_attributes.' .
                            $specialFields;
                        $this->addField($fieldId, $data);
                    }
                }
                continue;
            }

            if ($fields === Customer::ENTITY) {
                foreach ($types as $field => $data) {
                    if (!$this->displayFieldInCheckout($data)) {
                        continue;
                    }

                    $fieldValues = $data[DataHelper::FIELD_VALUES];
                    if (!is_array($fieldValues)) {
                        continue;
                    }

                    if ($fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_BOTH ||
                        $fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_SHIPPING
                    ) {
                        $specialFields = 'ea'. Customer::ENTITY. 'ea_'. $data[DataHelper::FIELD_IDENTIFIER];
                        $data['dataScope'] = 'shippingAddress.custom_attributes.' .
                            $specialFields;
                        $this->addField($field, $data);
                    }
                }
                continue;
            }

            foreach ($types as $field => $data) {
                $fieldValues = $data[DataHelper::FIELD_VALUES];
                if (!is_array($fieldValues)) {
                    continue;
                }

                if (!$this->displayFieldInCheckout($data)) {
                    continue;
                }

                if (array_key_exists(DataHelper::FIELD_SPECIFIC_POSITION, $fieldValues)) {
                    $specificLocation = $fieldValues[DataHelper::FIELD_SPECIFIC_POSITION];

                    $specialFields = 'ea'. CustomAttributes::ORDER_ENTITY. 'ea_'. $data[DataHelper::FIELD_IDENTIFIER];
                    $data['dataScope'] = 'shippingAddress.custom_attributes.' .
                        $specialFields;
                    $this->addToSpecificLocation($field, $specificLocation, $data);

                    continue;
                }

                if ($fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_BOTH ||
                    $fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_SHIPPING
                ) {
                    $specialFields = 'ea'. CustomAttributes::ORDER_ENTITY. 'ea_'. $data[DataHelper::FIELD_IDENTIFIER];
                    $data['dataScope'] = 'shippingAddress.custom_attributes.' .
                        $specialFields;
                    $this->addField($field, $data);
                }
            }
        }
        $this->addCustomValidators();

        return $this;
    }

    /**
     * Add a field with the needed params
     * @param $fieldId
     * @param array $params
     * @return $this
     */
    private function addField($fieldId, $params = [])
    {
        $field = [
            'config' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
            ],
            'dataScope' => '',
            'label' => '',
            'provider' => 'checkoutProvider',
            'sortOrder' => 5,
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'class' => '',
            'validation' => [
                'required-entry' => true
            ],
            'id' => $fieldId,
            'value' => 'Test',
            'default' => 0,
        ];

        $fieldData = array_replace_recursive($field, $params);

        $this->result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children'][$fieldId] = $fieldData;

        return $this;
    }

    public function addToSpecificLocation($fieldId, $specificLocation, $params = [])
    {
        $field = [
            'config' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'customScope' => 'shippingAddress.order_fields.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
            ],
            'dataScope' => '',
            'label' => '',
            'provider' => 'checkoutProvider',
            'sortOrder' => 5,
            'validation' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'class' => '',
            'id' => $fieldId
        ];

        $fieldData = array_replace_recursive($field, $params);
        $locations = $this->showOnAddress->toArrayLocations();
        $location = $locations[$specificLocation][DataHelper::LOCATION];

        $this->addCustomFieldSet($location);

        $this->result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children'][$location]
        ['children']['custom-checkout-form-container']['children']['custom-checkout-form-fieldset']
        ['children'][$fieldId] = $fieldData;

        return $this;
    }

    public function addCustomFieldSet($location)
    {
        if (isset($this->result['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children'][$location]
            ['children']['custom-checkout-form-container'])) {
            return $this->result;
        }

        $fieldSet = [
            'component' => 'Magento_Ui/js/form/form',
            'provider' => 'checkoutProvider',
            'config' => [
                'template' => 'Xtento_CustomAttributes/checkout/shipping-methods-checkout-form',
            ],
            'children' => [
                'custom-checkout-form-fieldset' => [
                    'component' => 'uiComponent',
                    'displayArea' => 'custom-checkout-form-fields',
                    'children' => []
                ]
            ]
        ];

        $this->result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children'][$location]
        ['children']['custom-checkout-form-container'] = $fieldSet;

        return $this->result;
    }

    /**
     * Add the validation system for the shipping address
     * @return mixed
     */
    private function addCustomValidators()
    {
        $this->result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['step-config']['children']['shipping-rates-validation']
        ['children'][self::SHIPPING_VALIDATORS]['component'] =
            'Xtento_CustomAttributes/js/view/checkout/validators/shipping-validator';

        return $this->result;
    }

    /**
     * Modify a field and add new properties, not implemented
     * @param $fieldId
     * @param $data
     * @return $this
     */
    private function fieldModifier($fieldId, $data)
    {
        $field = $this->result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children'][$fieldId];

        $fieldData = array_replace_recursive($field, $data);

        $this->result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children'][$fieldId] = $fieldData;

        return $this;
    }

    /**
     * @param $fieldData
     *
     * @return bool
     */
    private function displayFieldInCheckout($fieldData)
    {
        return in_array(Data::CHECKOUT, $fieldData['visible_on']);
    }
}
