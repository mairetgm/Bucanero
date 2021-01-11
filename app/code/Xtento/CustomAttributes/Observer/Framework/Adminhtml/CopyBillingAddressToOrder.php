<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Observer/Framework/Adminhtml/CopyBillingAddressToOrder.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Observer\Framework\Adminhtml;

use Xtento\CustomAttributes\Model\Sales\Address as ExtensionAddressAttributes;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class CopyAddressToRegisteredCustomer
 * @package Xtento\CustomAttributes\Observer\Framework\Adminhtml
 */
class CopyBillingAddressToOrder implements ObserverInterface
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
     * @event Xtento_CustomAttributes_Adminhtml_Observer_Sales_CopyBillingAddressToOrderr
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

        /** @var Address $orderAddress */
        $orderAddress = $observer->getSource();
        $quoteAddress = $observer->getTarget();

        $this->extensionAddressAttributes->extensionAttributes($orderAddress);

        if ($orderAddress instanceof Address) {
            $extensionAttributes = $orderAddress->getExtensionAttributes();

            if ($extensionAttributes) {

                /** @var DataObject $address */
                $orderAddress = $extensionAttributes->getCustomerAddress();

                if (!$orderAddress) {
                    return;
                }

                $customerAddressData = $orderAddress->getData();
                foreach ($customerAddressData as $key => $data) {
                    if ($data === null) {
                        continue;
                    }
                }
                $quoteAddress->setData($key, $data);
            }
        }

        return $this;
    }
}
