<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-01-28T15:17:54+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Sales/Address/OrderAddressModelPlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Sales\Address;

use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Api\Data\OrderAddressExtensionFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class OrderAddressModelPlugin
{
    /**
     * @var OrderAddressExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var DataObjectFactory
     */
    private $dataObject;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteria;

    private $addressRepository;

    /**
     * OrderAddressModelPlugin constructor.
     * @param OrderAddressExtensionFactory $extensionFactory
     * @param DataHelper $dataHelper
     * @param DataObjectFactory $dataObject
     * @param SearchCriteriaBuilder $searchCriteria

     */
    public function __construct(
        OrderAddressExtensionFactory $extensionFactory,
        DataHelper $dataHelper,
        DataObjectFactory $dataObject,
        SearchCriteriaBuilder $searchCriteria,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->extensionFactory  = $extensionFactory;
        $this->dataHelper        = $dataHelper;
        $this->dataObject        = $dataObject;
        $this->addressRepository = $addressRepository;
        $this->searchCriteria    = $searchCriteria;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSetOrder($subject, $result)
    {
        $extensionAttributes = $subject->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }

        $fieldHelperData = $this->dataHelper->createFields();

        $customerAddressId = $subject->getCustomerAddressId();
        $searchCriteriaBuilder = $this->searchCriteria;
        $searchCriteria = $searchCriteriaBuilder
            ->addFilter(
                'entity_id',
                $customerAddressId,
                'eq')
            ->create();
        $hasAddress = $this->addressRepository->getList($searchCriteria);
        $items = $hasAddress->getItems();

        if ($customerAddressId && !empty($items)) {
            $subjectData = $this->addressRepository->getById($customerAddressId);
            $customAttributes = $subjectData->getCustomAttributes();

            foreach ($customAttributes as $customAttribute) {
                $subject->setData($customAttribute->getAttributeCode(), $customAttribute->getValue());
            }
        }

        foreach ($fieldHelperData as $key => $fields) {
            if ($key === CustomAttributes::ADDRESS_ENTITY) {
                $data = $this->dataObject->create();
                $data->unsetData();
                $this->extensionAttributeDataProcessor($subject, $fields, $data);

                $extensionAttributes->setData(
                    $key,
                    $data
                );
            }

            if ($key === Customer::ENTITY) {
                $data = $this->dataObject->create();
                $data->unsetData();
                $this->extensionAttributeDataProcessor($subject, $fields, $data);

                $extensionAttributes->setData(
                    $key,
                    $data
                );
            }

            if ($key === CustomAttributes::ORDER_ENTITY) {
                $data = $this->dataObject->create();
                $data->unsetData();
                $this->extensionAttributeDataProcessor($subject, $fields, $data);

                $extensionAttributes->setData(
                    $key,
                    $data
                );
            }
        }

        $subject->setExtensionAttributes($extensionAttributes);

        return $result;
    }

    /**
     * @param $subject
     * @param $fields
     * @param $data
     */
    private function extensionAttributeDataProcessor($subject, $fields, $data)
    {
        foreach ($fields as $field) {
            $fieldValue = $subject->getData($field[DataHelper::FIELD_IDENTIFIER]);
            $data->setData($field[DataHelper::FIELD_IDENTIFIER], $fieldValue);
        }
    }
}
