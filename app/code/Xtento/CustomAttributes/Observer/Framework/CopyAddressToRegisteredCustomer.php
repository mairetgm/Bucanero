<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Observer/Framework/CopyAddressToRegisteredCustomer.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Observer\Framework;

use Xtento\CustomAttributes\Model\Api\Data\AttributeData;
use Xtento\CustomAttributes\Model\Quote\Address as ExtensionAddressAttributes;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Api\AttributeInterface;

/**
 * Class CopyAddressToCustomer
 * @package Xtento\Checkoutaddressfields\Observer\Framawork
 */
class CopyAddressToRegisteredCustomer implements ObserverInterface
{
    /**
     * @var CustomAttributesFactory
     */
    private $customAttributesFactory;

    /**
     * @var ExtensionAddressAttributes
     */
    private $extensionAddressAttributes;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * CopyAddressToRegisteredCustomer constructor.
     * @param AttributeValueFactory $customAttributesFactory
     * @param ExtensionAddressAttributes $extensionAddressAttributes
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        AttributeValueFactory $customAttributesFactory,
        ExtensionAddressAttributes $extensionAddressAttributes,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customAttributesFactory    = $customAttributesFactory;
        $this->extensionAddressAttributes = $extensionAddressAttributes;
        $this->customerRepository         = $customerRepository;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function execute(Observer $observer)
    {

        /** @var Address $address */
        $address = $observer->getSource();

        $this->extensionAddressAttributes->extensionAttributes($address);

        if ($address instanceof Address) {
            $extensionAttributes = $address->getExtensionAttributes();
            $sourceData = $observer->getSourceData();

            if ($extensionAttributes) {

                /** @var DataObject $customerAddress */
                $customerAddress = $extensionAttributes->getCustomerAddress();

                if (!$customerAddress) {
                    return;
                }

                $customAttributes = [];
                if ($customerAddress instanceof AttributeData) {
                    return;
                }

                $customerAddressData = $customerAddress->getData();
                foreach ($customerAddressData as $key => $data) {
                    if ($data === null) {
                        continue;
                    }
                    $customAttributes[$key] = [
                        AttributeInterface::ATTRIBUTE_CODE => $key,
                        AttributeInterface::VALUE => $data
                    ];
                }

                if (!empty($customAttributes)) {
                    $sourceData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] = $customAttributes;
                }
            }
        }
    }
}
