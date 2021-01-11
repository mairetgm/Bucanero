<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-09-17T19:14:33+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Checkout/BillingLayoutProcessor.php
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
 * Class BillingLayoutProcessor
 * @package Xtento\CustomAttributes\Plugin\Checkout
 */
class BillingLayoutProcessor
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
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutHelper;

    private $displayArea;

    /**
     * BillingLayoutProcessor constructor.
     *
     * @param Data $data
     * @param ShowOnAddress $showOnAddress
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     */
    public function __construct(
        DataHelper $data,
        ShowOnAddress $showOnAddress,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
        $this->data          = $data;
        $this->showOnAddress = $showOnAddress;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * @param LayoutProcessor $subject
     * @param array $result
     * @return array
     * @plugin xteea_checkout_shipping_address_fields
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
     * Add ro modify the fields in the settings recursively
     * @return $this
     */
    private function iterator()
    {
        if ($this->checkoutHelper->isDisplayBillingOnPaymentMethodAvailable()) {
            $this->displayArea = 'payments-list';
        } else {
            $this->displayArea = 'afterMethods';
        }

        $paymentForms = $this->result['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']
        [$this->displayArea]['children'];

        $paymentMethodForms = array_keys($paymentForms);

        if (!isset($paymentMethodForms)) {
            return $this->result;
        }

        foreach ($paymentMethodForms as $paymentMethodForm) {
            $paymentMethodCode = str_replace(
                '-form',
                '',
                $paymentMethodForm,
                $paymentMethodCode
            );

            $scope = $paymentMethodCode;
            if ($this->displayArea == 'afterMethods') {
                $paymentMethodForm = 'billing-address-form';
                $paymentMethodCode = 'billing-address';
                $scope = 'shared';
            }

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
                            $fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_BILLING
                        ) {
                            $specialFields = 'ea' . CustomAttributes::ADDRESS_ENTITY . 'ea_' . $fieldId;
                            $data['dataScope'] = 'billingAddress' .
                                $scope .
                                '.custom_attributes.' .
                                $specialFields;
                            $this->addField($fieldId, $paymentMethodForm, $scope, $data);
                        }
                    }
                    continue;
                }

                if ($fields === Customer::ENTITY) {
                    foreach ($types as $fieldId => $data) {
                        if (!$this->displayFieldInCheckout($data)) {
                            continue;
                        }

                        $fieldValues = $data[DataHelper::FIELD_VALUES];
                        if (!is_array($fieldValues)) {
                            continue;
                        }

                        if ($fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_BOTH ||
                            $fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_BILLING
                        ) {
                            $specialFields = 'ea' . Customer::ENTITY . 'ea_' . $data[DataHelper::FIELD_IDENTIFIER];
                            $data['dataScope'] = 'billingAddress' .
                                $scope .
                                '.custom_attributes.' .
                                $specialFields;
                            $this->addField($fieldId, $paymentMethodForm, $scope, $data);
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
                        $specialFields = 'ea' . CustomAttributes::ORDER_ENTITY .
                            'ea_' . $data[DataHelper::FIELD_IDENTIFIER];
                        $data['dataScope'] =
                            'custom_attributes.' .
                            $specialFields;

                        $this->addToSpecificLocation(
                            $field,
                            $paymentMethodForm,
                            $paymentMethodCode,
                            $specificLocation,
                            $data
                        );
                        continue;
                    }

                    if ($fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_BOTH ||
                        $fieldValues[DataHelper::AVAILABLE_ON] === DataHelper::AVAILABLE_ON_BILLING
                    ) {
                        $specialFields = 'ea' . CustomAttributes::ORDER_ENTITY .
                            'ea_' . $data[DataHelper::FIELD_IDENTIFIER];
                        $data['dataScope'] = 'billingAddress' .
                            $scope .
                            '.custom_attributes.' .
                            $specialFields;
                        $this->addField($field, $paymentMethodForm, $scope, $data);
                    }
                }
            }
        }

        $this->addCustomValidators();

        return $this;
    }

    /**
     * @param $fieldId
     * @param array $params
     * @param $paymentMethodForm
     * @param $paymentMethodCode
     * @return $this
     */
    private function addField($fieldId, $paymentMethodForm, $paymentMethodCode, $params = [])
    {
        $field = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'customScope' => 'billingAddress' . $paymentMethodCode . '.custom_attributes',
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
            'id' => $fieldId
        ];

        $fieldData = array_replace_recursive($field, $params);

        $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children'][$this->displayArea]['children'][$paymentMethodForm]['children']
        ['form-fields']['children'][$fieldId] = $fieldData;

        return $this;
    }

    public function addToSpecificLocation(
        $fieldId,
        $paymentMethodForm,
        $paymentMethodCode,
        $specificLocation,
        $params = []
    ) {
        $field = [
            'config' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'customScope' => 'customCheckoutForm',
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
            'id' => $fieldId
        ];

        $fieldData = array_replace_recursive($field, $params);

        $locations = $this->showOnAddress->toArrayLocations();
        $location = $locations[$specificLocation][DataHelper::LOCATION];

        $parentLocation = $locations[$specificLocation][DataHelper::PARENT_LOCATION];

        $this->addCustomFieldSet($location, $parentLocation);

        if (!$parentLocation) {
            $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'][$location]['children']
            ['custom-checkout-form-container']['children']['custom-checkout-form-fieldset']
            ['children'][$fieldId] = $fieldData;

            return $this;
        }

        $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children'][$parentLocation]['children'][$location]['children']
        ['custom-checkout-form-container']['children']['custom-checkout-form-fieldset']
        ['children'][$fieldId] = $fieldData;

        return $this;
    }

    public function addCustomFieldSet($location, $parentLocation)
    {
        if (isset($this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'][$location]['children']
            ['custom-checkout-form-container'])) {
            return $this;
        }

        if (isset($this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'][$parentLocation]['children'][$location]['children']
            ['custom-checkout-form-container'])) {
            return $this;
        }

        $fieldSet = [
            'component' => 'Magento_Ui/js/form/form',
            'provider' => 'checkoutProvider',
            'config' => [
                'template' => 'Xtento_CustomAttributes/checkout/billing-methods-checkout-form',
            ],
            'children' => [
                'custom-checkout-form-fieldset' => [
                    'component' => 'uiComponent',
                    'displayArea' => 'custom-checkout-form-fields',
                    'children' => []
                ]
            ]
        ];

        if (!$parentLocation) {
            $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'][$location]['children']
            ['custom-checkout-form-container'] = $fieldSet;

            return $this;
        }

        $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children'][$parentLocation]['children'][$location]['children']
        ['custom-checkout-form-container'] = $fieldSet;

        return $this;
    }

    /**
     * Add the validation system for the billing address
     * @return mixed
     */
    private function addCustomValidators()
    {
        $this->result['components']['checkout']['children']['steps']['children']['billing-step']
        ['children']['payment']['children']['additional-payment-validator']
        ['children']['field-validator']
        ['component'] = 'Xtento_CustomAttributes/js/view/checkout/validators/billing-validator';

        return $this->result;
    }

    /**
     * Modify a field and add new properties, not implemented
     * @param $fieldId
     * @param $data
     * @return $this
     */
    private function fieldModifier($fieldId, $data, $paymentMethodForm)
    {
        $hasFormFields = array_key_exists(
            'form-fields',
            $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children'][$this->displayArea]['children'][$paymentMethodForm]['children']
        );

        if (!$hasFormFields) {
            return $this;
        }

        $field = $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children'][$this->displayArea]['children'][$paymentMethodForm]['children']
        ['form-fields']['children'][$fieldId];

        $fieldData = array_replace_recursive($field, $data);

        $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children'][$this->displayArea]['children'][$paymentMethodForm]['children']
        ['form-fields']['children'][$fieldId] = $fieldData;

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
