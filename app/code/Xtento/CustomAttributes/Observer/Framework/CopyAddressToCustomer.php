<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Observer/Framework/CopyAddressToCustomer.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Observer\Framework;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\DataObject;
use Xtento\CustomAttributes\Block\Checkout\Address\Fields\ShippingLayoutProcessor;
use Xtento\CustomAttributes\Model\Data\OrderFields;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Class CopyAddressToCustomer
 * @package Xtento\Checkoutaddressfields\Observer\Framawork
 */
class CopyAddressToCustomer implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomAttributesFactory
     */
    private $customAttributesFactory;

    /**
     * CopyAddressToCustomer constructor.
     * @param LoggerInterface $logger
     * @param AttributeValueFactory $customAttributesFactory
     * @event core_copy_fieldset_order_address_to_customer
     */
    public function __construct(
        LoggerInterface $logger,
        AttributeValueFactory $customAttributesFactory
    ) {
        $this->logger                  = $logger;
        $this->customAttributesFactory = $customAttributesFactory;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer) {

        /** @var Order\Address $source */
        $source = $observer->getSource();

        if ($source instanceof Order\Address) {
            $extensionAttributes = $source->getExtensionAttributes();

            if ($extensionAttributes) {

                /** @var DataObject $customer */
                $customer = $extensionAttributes->getCustomer();

                if (!$customer) {
                    return;
                }

                $customAttributes = [];
                $customerData = $customer->getData();
                foreach ($customerData as $key => $data) {
                    if ($data === null) {
                        continue;
                    }
                    $customAttributesFactory = $this->customAttributesFactory->create();
                    $customAttributes[$key] = $customAttributesFactory->setAttributeCode($key)->setValue($data);
                }

                /** @var DataObject $target */
                $target = $observer->getTarget();
                if (!empty($customAttributes)) {
                    $target->addData([CustomAttributesDataInterface::CUSTOM_ATTRIBUTES => $customAttributes]);
                }
            }
        }
    }
}