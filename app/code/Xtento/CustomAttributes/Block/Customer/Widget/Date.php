<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-03-27T20:28:24+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Customer/Widget/Date.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Customer\Widget;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Helper\Data;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Block\Widget\Dob;
use Magento\Customer\Helper\Address;
use Magento\Framework\Data\Form\FilterFactory;
use Magento\Framework\View\Element\Html\Date as DateElement;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use IntlDateFormatter;
use Magento\Customer\Api\AddressRepositoryInterface as AddressRepository;

class Date extends Dob
{
    private $customerSession;

    private $timezoneInterface;

    private $dateTime;

    public $addressRepository;

    /**
     * Date constructor.
     *
     * @param Context $context
     * @param Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param DateElement $dateElement
     * @param FilterFactory $filterFactory
     * @param Session $customerSession
     * @param TimezoneInterface $timezoneInterface
     * @param DateTime $dateTime
     * @param AddressRepository $addressRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        DateElement $dateElement,
        FilterFactory $filterFactory,
        Session $customerSession,
        TimezoneInterface $timezoneInterface,
        DateTime $dateTime,
        AddressRepository $addressRepository,
        array $data = []
    ) {
        $this->customerSession   = $customerSession;
        $this->timezoneInterface = $timezoneInterface;
        $this->dateTime          = $dateTime;
        $this->addressRepository = $addressRepository;
        $this->dateElement = $dateElement;

        parent::__construct(
            $context,
            $addressHelper,
            $customerMetadata,
            $dateElement,
            $filterFactory,
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
            'Xtento_CustomAttributes::customer/widget/date.phtml'
        );
    }

    public function isRequired()
    {
        $field = $this->getField();
        return (bool) $field->getFieldRequired();
    }

    public function getFieldHtml()
    {
        $field = $this->getField();
        $attribute = $field->getData(Data::ATTRIBUTE_DATA);
        $attributeCode = $attribute->getData(FieldsInterface::ATTRIBUTE_CODE);
        $value = $this->customAttributeValue($attributeCode);
        $dateAndTime = $field->getData(FieldsInterface::FRONTEND_OPTION);
        $myValue = $this->customAttributeValue($attributeCode);

        if ($this->getIsDisabledOnFrontend()) {
            return $value;
        }

        $time = false;
        if ($dateAndTime) {
            $time = 'hh:mm';
        }

        $this->dateElement->setData(
            [
                'extra_params' => $this->getHtmlExtraParams(),
                'name' => $field->getAttributeCode(),
                'id' => $field->getAttributeCode(),
                'class' => $this->getHtmlClass(),
                'value' => $myValue,
                'date_format' => $this->getDateFormat(),
                'time_format' => $time,
                'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                'years_range' => '-120y:c+nn',
                'change_month' => 'true',
                'change_year' => 'true',
                'show_on' => 'both',
                'first_day' => $this->getFirstDay()
            ]
        );

        return $this->dateElement->getHtml();
    }

    /**
     * @param $code
     * @return mixed
     */
    public function customAttributeValue($code)
    {
        $customer = $this->customerSession->getCustomer();
        $value = $customer->getData($code);
        $field = $this->getData('field');
        $frontendOption = $field->getFrontendOption();
        $isTime = false;
        if ($frontendOption == 1) {
            $isTime = true;
        }

        $addressValue = $this->isAddress($code);

        if ($addressValue) {
            $addressValueTime = strtotime($addressValue);

            $date = $this->timezoneInterface->formatDate(
                $this->timezoneInterface->date($this->dateTime->date('Y-m-d h:m:s', $addressValueTime + 99999)),
                IntlDateFormatter::SHORT,
                $isTime
            );
            return $date;
        }

        if ($value) {
            $addressValueTime = strtotime($value);

            $date = $this->timezoneInterface->formatDate(
                $this->timezoneInterface->date($this->dateTime->date('Y-m-d h:m:s', $addressValueTime + 99999)),
                IntlDateFormatter::SHORT,
                $isTime
            );

            return $date;
        }

        /** @var Attribute $attributeData */
        $attributeData = $this->getField()->getData(Data::ATTRIBUTE_DATA);
        $defaultValueDate = $attributeData->getDefaultValue();

        if ($attributeData->getDefaultValue()) {
            $addressValueTime = strtotime($defaultValueDate);
            $date = $this->timezoneInterface->formatDate(
                $this->timezoneInterface->date($this->dateTime->date('Y-m-d h:m:s', $addressValueTime)),
                IntlDateFormatter::SHORT,
                $isTime
            );

            return $date;
        }
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

        return false;
    }

    public function getIsDisabledOnFrontend()
    {
        return (bool)$this->getField()->getData('disabled_on_frontend');
    }
}
