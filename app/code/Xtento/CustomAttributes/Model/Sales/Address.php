<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Sales/Address.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Sales;

use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Api\Data\OrderAddressExtensionFactory as AddressExtensionFactory;
use Magento\Customer\Model\Customer;

/**
 * Class Address can be used to insert extension attributes to quote address
 * @package Xtento\CustomAttributes\Model\Quote
 */
class Address
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
     * OrderAddressModelPlugin constructor.
     * @param AddressExtensionFactory $extensionFactory
     * @param DataHelper $dataHelper
     * @param DataObjectFactory $dataObject
     */
    public function __construct(
        AddressExtensionFactory $extensionFactory,
        DataHelper $dataHelper,
        DataObjectFactory $dataObject
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->dataHelper       = $dataHelper;
        $this->dataObject       = $dataObject;
    }

    /**
     * Loads order attributes entity extension attributes
     *
     * @return $this
     */
    public function extensionAttributes($address)
    {
        $extensionAttributes = $address->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }

        $fieldHelperData = $this->dataHelper->createFields();

        foreach ($fieldHelperData as $key => $fields) {
            if ($key === CustomAttributes::ADDRESS_ENTITY) {
                $data = $this->dataObject->create();
                $data->unsetData();
                $this->extensionAttributeDataProcessor($address, $fields, $data);

                $extensionAttributes->setData(
                    $key,
                    $data
                );
            }

            if ($key === Customer::ENTITY) {
                $data = $this->dataObject->create();
                $data->unsetData();
                $this->extensionAttributeDataProcessor($address, $fields, $data);

                $extensionAttributes->setData(
                    $key,
                    $data
                );
            }

            if ($key === CustomAttributes::ORDER_ENTITY) {
                $data = $this->dataObject->create();
                $data->unsetData();
                $this->extensionAttributeDataProcessor($address, $fields, $data);

                $extensionAttributes->setData(
                    $key,
                    $data
                );
            }
        }

        $address->setExtensionAttributes($extensionAttributes);

        return $this;
    }

    /**
     * @param $address
     * @param $fields
     * @param $data
     */
    private function extensionAttributeDataProcessor($address, $fields, $data)
    {
        foreach ($fields as $field) {
            $fieldValue = $address->getData($field[DataHelper::FIELD_IDENTIFIER]);
            $data->setData($field[DataHelper::FIELD_IDENTIFIER], $fieldValue);
        }
    }
}
