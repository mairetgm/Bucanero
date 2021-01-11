<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-03-27T20:28:24+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Customer/Widget/DropDown.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Customer\Widget;

use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Block\Widget\Gender;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Data\Option;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Api\AddressRepositoryInterface as AddressRepository;

class DropDown extends Gender
{
    /**
     * @var Option
     * */
    private $option;

    /**
     * @var Session
     */
    public $customerSession;

    /**
     * @var AddressRepository
     */
    public $addressRepository;

    /**
     * DropDown constructor.
     * @param Context $context
     * @param Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param CustomerRepositoryInterface $customerRepository
     * @param Session $customerSession
     * @param Option $option
     * @param array $data
     */
    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        Option $option,
        AddressRepository $addressRepository,
        array $data = []
    ) {
        $this->option            = $option;
        $this->customerSession   = $customerSession;
        $this->addressRepository = $addressRepository;

        parent::__construct(
            $context,
            $addressHelper,
            $customerMetadata,
            $customerRepository,
            $customerSession,
            $data
        );
        $this->_isScopePrivate = true;
    }

    /**
     * Sets the template
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate(
            'Xtento_CustomAttributes::customer/widget/dropdown.phtml'
        );
    }

    public function getOptions()
    {
        $field = $this->getField();
        $this->field = $field;

        $attributeCode = $field->getData('attribute_code');
        $metadata =  $this->_getAttribute($attributeCode);

        if ($metadata instanceof AttributeMetadata) {
            $options = $this->_getAttribute($attributeCode)->getOptions();
            return $options;
        }

        $options = $field->getData(DataHelper::ATTRIBUTE_OPTIONS_DATA);
        return $options;
    }

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
        $attributeData = $this->getField()->getData(DataHelper::ATTRIBUTE_DATA);

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
            $attributeData = $this->getField()->getData(DataHelper::ATTRIBUTE_DATA);

            return $attributeData->getDefaultValue();
        }
    }

    public function getIsDisabledOnFrontend()
    {
        return (bool)$this->getField()->getData('disabled_on_frontend');
    }
}

