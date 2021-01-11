<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-03-27T20:28:24+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Customer/Widget/AreaText.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Customer\Widget;

use Xtento\CustomAttributes\Helper\Data;

class AreaText extends Text
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
            'Xtento_CustomAttributes::customer/widget/areatext.phtml'
        );
    }

    /**
     * @param $code
     * @return mixed
     */
    public function customAttributeValue($code)
    {
        $customer = $this->customerSession->getCustomer();
        $value = $customer->getData($code);

        if ($addressValue = $this->isAddress($code)) {
            return $addressValue;
        }

        if ($value) {
            return $value;
        }

        /** @var Attribute $attributeData */
        $attributeData = $this->getField()->getData(Data::ATTRIBUTE_DATA);

        return $attributeData->getDefaultValue();
    }

    public function isAddress($code)
    {
        $request = $this->getRequest();

        if ($addressId = $request->getParam('id')) {
            $address = $this->addressRepository->getById($addressId);

            $customAttribute = $address->getCustomAttribute($code);

            if ($customAttribute) {
                return $customAttribute->getValue();
            }

            /** @var Attribute $attributeData */
            $attributeData = $this->getField()->getData(Data::ATTRIBUTE_DATA);

            return $attributeData->getDefaultValue();
        }
    }

    public function getIsDisabledOnFrontend()
    {
        return (bool)$this->getField()->getData('disabled_on_frontend');
    }
}
