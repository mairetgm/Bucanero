<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-06-14T13:14:57+00:00
 * File:          app/code/Xtento/CustomAttributes/Observer/Sales/OrderLoadAfter.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Observer\Sales;

use Xtento\CustomAttributes\Model\Api\Data\OrderAdditionalInfo;
use Xtento\CustomAttributes\Model\FieldsRepository;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Model\Api\Data\OrderAdditionalInfoFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Store\Model\StoreManagerInterface;

class OrderLoadAfter implements ObserverInterface
{
    private $extensionFactory;

    private $additionalInfoFactory;

    private $fieldsRepository;

    private $searchCriteriaBuilder;

    private $filterBuilder;

    private $storeManager;

    public function __construct(
        OrderExtensionFactory $extensionFactory,
        OrderAdditionalInfoFactory $additionalInfoFactory,
        FieldsRepository $fieldsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->extensionFactory      = $extensionFactory;
        $this->additionalInfoFactory = $additionalInfoFactory;
        $this->fieldsRepository      = $fieldsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder         = $filterBuilder;
        $this->storeManager          = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();

        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }

        $fields = $this->orderFields();

        $additionalInfo = [];
        foreach ($fields as $field) {
            /** @var OrderAdditionalInfo $additionalInfoFactory */
            $additionalInfoFactory = $this->additionalInfoFactory->create();
            $additionalInfoFactory->setKey($field);
            $additionalInfoFactory->setValue($order->getData($field));
            $additionalInfo[] = $additionalInfoFactory;
        }

        $extensionAttributes->setOrderField($additionalInfo);
        $order->setExtensionAttributes($extensionAttributes);
    }

    private function orderFields(): array
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder;

        $store = $this->filterBuilder
            ->setField(FieldsInterface::STORE_ID)
            ->setValue($this->storeManager->getStore()->getId())
            ->setConditionType('in')
            ->create();

        $active = $this->filterBuilder
            ->setField(FieldsInterface::IS_ACTIVE)
            ->setValue(1)
            ->setConditionType('eq')
            ->create();

        $addressType = $this->filterBuilder
            ->setField(FieldsInterface::ATTRIBUTE_TYPE)
            ->setValue(CustomAttributes::ORDER_ENTITY)
            ->setConditionType('eq')
            ->create();

        $searchCriteriaBuilder
            ->addFilters([$addressType]);
        $searchCriteriaBuilder
            ->addFilters([$active]);
        $searchCriteriaBuilder
            ->addFilters([$store]);

        $searchCriteria = $searchCriteriaBuilder->create();

        $list = $this->fieldsRepository->getList($searchCriteria);
        $items = $list->getItems();

        $fields = [];
        if (empty($items)) {
            return $fields;
        }

        /** @var Fields $item */
        foreach ($items as $item) {
            $fields[] = $item->getAttributeCode();
        }

        return $fields;
    }
}