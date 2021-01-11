<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-18T15:29:25+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Address/CustomerAttributes.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Address;

use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Block\Customer\Dashboard\CustomerAttributes as CustomerAttributesDashboard;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Api\FilterBuilder;
use IntlDateFormatter;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;

class CustomerAttributes extends \Magento\Framework\View\Element\Template
{
    private $dataHelper;

    private $filterBuilder;

    private $timezoneInterface;

    private $dateTime;

    private $url;

    private $address;

    public function __construct(
        Template\Context $context,
        DataHelper $dataHelper,
        FilterBuilder $filterBuilder,
        TimezoneInterface $timezoneInterface,
        DateTime $dateTime,
        UrlInterface $url,
        array $data = []
    ) {
        $this->dataHelper        = $dataHelper;
        $this->filterBuilder     = $filterBuilder;
        $this->timezoneInterface = $timezoneInterface;
        $this->dateTime          = $dateTime;
        $this->url               = $url;

        parent::__construct($context, $data);
    }

    public function byAddress($address = false)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function fields()
    {
        $filters = [
            $this->filterBuilder
                ->setField(FieldsInterface::ATTRIBUTE_TYPE)
                ->setValue(CustomAttributes::ADDRESS_ENTITY)
                ->create()
        ];

        $fields = $this->dataHelper->createFields(
            $filters,
            CustomerAttributesDashboard::CUSTOMER_ACCOUNT
        );

        if (empty($fields)) {
            return false;
        }

        return $fields;
    }

    /**
     * @param Attribute $attribute
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addValues($attribute)
    {
        $address = $this->address;
        $customAttributes = $address->getCustomAttributes();
        $attributeCode = $attribute->getAttributeCode();

        if (!isset($customAttributes[$attributeCode])) {
            return null;
        }

        $customerData = $customAttributes[$attributeCode];

        $value = $customerData->getValue();

        $optionValue = $this->optionValues($attribute, $value);
        if ($optionValue) {
            $value = $optionValue;
        }

        if ($customerData) {
            if ($attribute->getFrontendInput() === InputType::BOOLEAN) {
                return $value = 1 ? __('No') : __('Yes');
            }

            if ($attribute->getFrontendInput() === InputType::DATE) {
                $date = $this->timezoneInterface->formatDate(
                    $this->timezoneInterface->date($this->dateTime->date($value)),
                    IntlDateFormatter::SHORT,
                    false
                );

                return $date;
            }

            return $value;
        }

        return null;
    }

    public function optionValues($attribute, $value)
    {
        $options  = $attribute->getOptions();

        if (empty($options)) {
            return false;
        }

        $values = explode(',', $value);

        $labels = [];
        /** @var Option $option */
        foreach ($options as $option) {
            $value = $option->getValue();
            $label = $option->getLabel();

            if (in_array($value, $values)) {
                $labels[] = $label;
            }
        }

        if (empty($labels)) {
            return false;
        }

        return implode(',', $labels);
    }

    public function getMediaDownloadLink()
    {
        return $this->url->getUrl('xtento_customattributes/index/download/file/');
    }
}