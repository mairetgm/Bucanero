<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Quote/Model/ShippingAddressManagementPlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Quote\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class ShippingAddressManagementPlugin
 * @package Xtento\CustomAttributes\Plugin\Quote\Model
 */
class ShippingAddressManagementPlugin
{

    private $addressRepository;

    /**
     * ShippingAddressManagementPlugin constructor.
     *
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(AddressRepositoryInterface $addressRepository)
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param $subject
     * @param $cartId
     * @param AddressInterface $address
     *
     * @throws CouldNotSaveException
     * @plugin xtea_checkout_shipping_quote_model_shippingaddressmanagement
     */
    //@codingStandardsIgnoreLine
    public function beforeAssign(
        $subject,
        $cartId,
        $address
    ) {
        $customerAddressId = $address->getCustomerAddressId();
        if ($customerAddressId) {
            $addressData = $this->addressRepository->getById($customerAddressId);
            $customAttributes = $addressData->getCustomAttributes();

            foreach ($customAttributes as $customAttribute) {
                $address->setData($customAttribute->getAttributeCode(), $customAttribute->getValue());
            }
        }

        $extensionAttributes = $address->getExtensionAttributes();
        if ($extensionAttributes) {
            try {
                $customerAddressExtension = $extensionAttributes->getCustomerAddress();

                if (!$customerAddressExtension) {
                    return;
                }

                $addressValues = $customerAddressExtension->getValue();

                foreach ($addressValues as $field => $data) {
                    $address->setData($field, $data);
                }

                $customerExtension = $extensionAttributes->getCustomer();

                if (!$customerExtension) {
                    return;
                }

                $customerValues = $customerExtension->getValue();

                foreach ($customerValues as $field => $data) {
                    $address->setData($field, $data);
                }
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __('A custom field could not be added to the address: %1', $e->getMessage()), $e
                );
            }
        }
    }
}
