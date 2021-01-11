<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-10-10T08:35:15+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/FieldProcessor/AddValue.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\FieldProcessor;

use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Magento\Eav\Model\Attribute;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\AddressRepository;

class AddValue
{
    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * AddValue constructor.
     *
     * @param DataHelper $dataHelper
     * @param AddressRepository $addressRepository
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        DataHelper $dataHelper,
        AddressRepository $addressRepository,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->dataHelper        = $dataHelper;
        $this->addressRepository = $addressRepository;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param Attribute $attribute
     * @param Order $order
     * @param bool $returnRawData If set to true, it will NOT return translated option values, useful if you need to get the internal value for example when rendering a select
     *
     * @return mixed
     */
    public function addValues($attribute, $order, $returnRawData = false)
    {
        $attributeCode = $attribute->getAttributeCode();
        $orderData = $order->getData($attributeCode);

        if ($returnRawData) {
            $options = [];
        } else {
            $options = $this->dataHelper->getAdminOptionValues(0, $attribute);
        }

        $result = $this->dataObjectFactory->create();

        if ($orderData !== false) {
            if (!empty($options)) {
                foreach ($options as $option) {
                    if ($option['value'] === $orderData) {
                        return $result->setData('value', $option['label']);
                    }
                }
            }

            if ($attribute->getFrontendInput() === InputType::BOOLEAN) {
                $bool = $this->dataHelper->yesNoCheckout();
                $boolValue = $bool[$orderData + 1]['label'];
                return $result->setData('value', $boolValue);
            } else if ($attribute->getFrontendInput() === InputType::MULTI_SELECT) {
                $values = explode(',', $orderData);
                if ($returnRawData) {
                    return $result->setData('value', implode(',', $values));
                } else {
                    $labels = [];
                    foreach ($values as $value) {
                        if (isset($options[$value]['label'])) {
                            $labels[] = $options[$value]['label'];
                        }
                    }
                    return $result->setData('value', implode(', ', $labels));
                }
            }

            return $result->setData('value', $orderData);
        }

        $billingAddressId = $order->getBillingAddress()->getEntityId();
        $billingAddress = $this->addressRepository->get($billingAddressId);
        $billingAddressData = $billingAddress->getData($attributeCode);

        if ($billingAddressData) {
            $result->setData('billing_value', $billingAddressData);

            if (!empty($options) && $attribute->getFrontendInput() !== InputType::MULTI_SELECT) {
                $result->setData('billing_value', $options[$billingAddressData]['label']);
            }

            if ($attribute->getFrontendInput() === InputType::MULTI_SELECT) {
                $values = explode(',', $billingAddressData);

                $labels = [];
                foreach ($values as $value) {
                    if (isset($options[$value]['label'])) {
                        $labels[] = $options[$value]['label'];
                    }
                }
                $result->setData('billing_value', implode(', ', $labels));
            } elseif ($attribute->getFrontendInput() === InputType::BOOLEAN) {
                $bool = $this->dataHelper->yesNoCheckout();
                $boolValue = $bool[$orderData + 1]['label'];
                $result->setData('billing_value', $boolValue);
            }

            return $result;
        }

        $shippingAddressId = $order->getShippingAddress()->getEntityId();
        $shippingAddress = $this->addressRepository->get($shippingAddressId);
        $shippingAddressData = $shippingAddress->getData($attributeCode);

        if ($shippingAddressData) {
            $result->setData('shipping_value', $shippingAddressData);

            if (!empty($options) && $attribute->getFrontendInput() !== InputType::MULTI_SELECT) {
                $result->setData('shipping_value', $options[$shippingAddressData]['label']);
            }

            if ($attribute->getFrontendInput() === InputType::MULTI_SELECT) {
                $values = explode(',', $shippingAddressData);

                $labels = [];
                foreach ($values as $value) {
                    $labels[] = $options[$value]['label'];
                }
                $result->setData('shipping_value', implode(', ', $labels));
            } elseif ($attribute->getFrontendInput() === InputType::BOOLEAN) {
                $bool = $this->dataHelper->yesNoCheckout();
                $boolValue = $bool[$orderData + 1]['label'];
                $result->setData('shipping_value', $boolValue);
            }

            return $result;
        }

        return $result;
    }

}