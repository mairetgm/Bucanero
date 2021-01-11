<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-03-27T20:28:24+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Customer/Widget/Boolean.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Customer\Widget;

use Xtento\CustomAttributes\Helper\Data as DataHelper;

class Boolean extends DropDown
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
            'Xtento_CustomAttributes::customer/widget/boolean.phtml'
        );
    }

    public function customAttributeValue($code)
    {
        $customer = $this->customerSession->getCustomer();
        $value = $customer->getData($code);
        if ($addressValue = $this->isAddress($code)) {
            return $addressValue;
        }
        if ($value !== 0) {
            return $value;
        }
        /** @var Attribute $attributeData */
        $attributeData = $this->getField()->getData(DataHelper::ATTRIBUTE_DATA);
        $defaultValue = $attributeData->getDefaultValue();
        return $defaultValue;
    }

    public function getIsDisabledOnFrontend()
    {
        return (bool)$this->getField()->getData('disabled_on_frontend');
    }
}
