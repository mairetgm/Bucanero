<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-03-27T20:28:24+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Customer/Widget/MultipleSelect.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Customer\Widget;

use Xtento\CustomAttributes\Helper\Data as DataHelper;

class MultipleSelect extends DropDown
{

    /**
     * Sets the template
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate(
            'Xtento_CustomAttributes::customer/widget/mutiple.phtml'
        );
    }

    public function getOptions()
    {
        $field = $this->getField();
        $attributeCode = $field->getData('attribute_code');

        $metadata =  $this->_getAttribute($attributeCode);
        if ($metadata instanceof AttributeMetadata) {
            $options = $this->_getAttribute($attributeCode)->getOptions();
            array_shift($options);
            return $options;
        }

        $options = $field->getData(DataHelper::ATTRIBUTE_OPTIONS_DATA);
        return $options;
    }

    public function customAttributeValue($code)
    {
        $customer = $this->customerSession->getCustomer();
        $value = $customer->getData($code);

        return $value;
    }

    public function customAttributeValues($code)
    {
        $customer = $this->customerSession->getCustomer();
        $value = $customer->getData($code);

        if ($addressValue = $this->isAddress($code)) {
            return explode(',', $addressValue);
        }

        $values = explode(',', $value);

        return $values;
    }

    public function getIsDisabledOnFrontend()
    {
        return (bool)$this->getField()->getData('disabled_on_frontend');
    }
}
