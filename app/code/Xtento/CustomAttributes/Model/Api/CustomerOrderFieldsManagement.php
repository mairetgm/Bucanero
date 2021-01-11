<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2020-04-03T14:55:40+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Api/CustomerOrderFieldsManagement.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Api;

use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\GridPool;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Api\Data\OrderCustomerFieldsInterface;
use Xtento\CustomAttributes\Api\CustomerOrderFieldsManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Xtento\CustomAttributes\Helper\Data as DataHelper;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Magento\Framework\Api\FilterBuilder;

class CustomerOrderFieldsManagement implements CustomerOrderFieldsManagementInterface
{
    /**
     * @var OrderFieldsManagementInterface
     */
    private $orderCustomerFields;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var GridPool
     */
    protected $gridPool;

    /**
     * CustomerOrderFieldsManagement constructor.
     *
     * @param OrderCustomerFieldsInterface $orderCustomerFields
     * @param CartRepositoryInterface $quoteRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param DataHelper $dataHelper
     * @param FilterBuilder $filterBuilder
     * @param GridPool $gridPool
     */
    public function __construct(
        OrderCustomerFieldsInterface $orderCustomerFields,
        CartRepositoryInterface $quoteRepository,
        OrderRepositoryInterface $orderRepository,
        DataHelper $dataHelper,
        FilterBuilder $filterBuilder,
        GridPool $gridPool
    ) {
        $this->orderCustomerFields = $orderCustomerFields;
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->dataHelper = $dataHelper;
        $this->filterBuilder = $filterBuilder;
        $this->gridPool = $gridPool;
    }

    public function saveFields(
        $cartId,
        OrderCustomerFieldsInterface $fields
    ) {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        $data = $fields->getFields();

        foreach ($data as $field => $value) {
            if ($value !== '') {
                $quote->setData($field, $value);
            }
        }

        try {
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('The order fields could not be saved'));
        }

        return null;
    }

    /**
     * @param int $id
     * @param OrderCustomerFieldsInterface $fields
     *
     * @return mixed|null
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function updateFields(
        $id,
        OrderCustomerFieldsInterface $fields
    ) {
        /** @var Order $order */
        $order = $this->orderRepository->get($id);
        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Order %1 not found.', $id));
        }

        // Load all Custom Attributes
        $filters = [
            $this->filterBuilder
                ->setField(FieldsInterface::ATTRIBUTE_TYPE)
                ->setValue(CustomAttributes::ORDER_ENTITY)
                ->create()
        ];
        $allCustomAttributes = $this->dataHelper->createFields($filters, 'api', null, false, true);

        $data = $fields->getFields();
        foreach ($data as $field => $value) {
            // Check is order attribute
            if (!isset($allCustomAttributes['order_field']) || !isset($allCustomAttributes['order_field'][$field])) {
                continue; // Not a custom attribute that can be updated
            }
            $order->setData($field, $value);
        }

        try {
            $this->orderRepository->save($order);
            $this->gridPool->refreshByOrderId($order->getId()); // Required as in webapi calls otherwise the grid isn't updated
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('The order fields could not be updated.'));
        }

        return null;
    }
}