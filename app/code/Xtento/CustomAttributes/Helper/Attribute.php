<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-06-14T13:14:57+00:00
 * File:          app/code/Xtento/CustomAttributes/Helper/Attribute.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Helper;

use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Xtento\CustomAttributes\Model\FieldsRepository;

class Attribute extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var FieldsRepository
     */
    protected $fieldsRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteria;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Attribute constructor.
     *
     * @param Context $context
     * @param DateTime $dateTime
     * @param FieldsRepository $fieldsRepository
     * @param SearchCriteriaBuilder $searchCriteria
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        FieldsRepository $fieldsRepository,
        SearchCriteriaBuilder $searchCriteria,
        FilterBuilder $filterBuilder
    ) {
        parent::__construct($context);
        $this->dateTime = $dateTime;
        $this->fieldsRepository = $fieldsRepository;
        $this->searchCriteria = $searchCriteria;
        $this->filterBuilder = $filterBuilder;
    }


    // Methods to be used by developers

    /**
     * @param $customer
     * @param $attribute
     *
     * @return bool|\Magento\Framework\Phrase|string|null
     */
    public function getCustomerAttributeText($customer, $attribute)
    {
        $customAttributes = $customer->getCustomAttributes();
        $attributeCode = $attribute->getAttributeCode();
        /*$items = $this->getCustomAttributes(false, $attribute);
        $current = end($items);*/

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
                $dateTime = $this->dateTime;
                $valueDateTime = $dateTime->date('Y-m-d H:i:s', $value);

                return $valueDateTime;
            }

            return $value;
        }

        return null;
    }

    // Internal helpers

    /**
     * @param bool $entity
     * @param bool $attribute
     *
     * @return mixed
     */
    public function getCustomAttributes($entity = false, $attribute = false)
    {
        $searchCriteriaBuilder = $this->searchCriteria;
        if ($attribute !== false) {
            $searchCriteria = $searchCriteriaBuilder
                ->addFilter(
                    'attribute_code',
                    $attribute->getAttributeCode(),
                    'eq'
                )
                ->create();
        } else if ($entity !== false) {
            $entityFilter = $this->filterBuilder
                ->setField(FieldsInterface::ATTRIBUTE_TYPE)
                ->setValue(CustomAttributes::CUSTOMER_ENTITY)
                ->setConditionType('eq')
                ->create();
            $searchCriteria = $searchCriteriaBuilder->addFilters([$entityFilter])->create();
        } else {
            $searchCriteria = $searchCriteriaBuilder->create();
        }
        /** @var \Magento\Framework\Api\SearchCriteria $fieldList */
        $fieldList = $this->fieldsRepository->getList($searchCriteria);
        $items = $fieldList->getItems();
        return $items;
    }

    /**
     * @param $attribute
     * @param $value
     *
     * @return bool|string
     */
    public function optionValues($attribute, $value)
    {
        $options = $attribute->getOptions();

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
}


