<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-05-14T12:04:32+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Sales/AdminOrder/Create.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Sales\AdminOrder;

use Xtento\CustomAttributes\Model\CustomAttributes;
use Magento\Sales\Model\AdminOrder\Create as CreateOrder;
use Magento\Sales\Model\Order;

class Create extends CreateOrder
{
    /**
     * @param Order $order
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function initFromOrder(Order $order)
    {
        $session = $this->getSession();
        $session->setData($order->getReordered() ? 'reordered' : 'order_id', $order->getId());
        $session->setCurrencyId($order->getOrderCurrencyCode());
        /* Check if we edit guest order */
        $session->setCustomerId($order->getCustomerId() ?: false);
        $session->setStoreId($order->getStoreId());

        /* Initialize catalog rule data with new session values */
        $this->initRuleData();
        foreach ($order->getItemsCollection($this->_salesConfig->getAvailableProductTypes(), true) as $orderItem) {
            /* @var $orderItem \Magento\Sales\Model\Order\Item */
            if (!$orderItem->getParentItem()) {
                $qty = $orderItem->getQtyOrdered();
                if (!$order->getReordered()) {
                    $qty -= max($orderItem->getQtyShipped(), $orderItem->getQtyInvoiced());
                }

                if ($qty > 0) {
                    $item = $this->initFromOrderItem($orderItem, $qty);
                    if (is_string($item)) {
                        throw new \Magento\Framework\Exception\LocalizedException(__($item));
                    }
                }
            }
        }

        /** @var Order\Address $shippingAddress */
        $shippingAddress = $order->getShippingAddress();
        /** @var Order\Address $billingAddress */
        $billingAddress = $order->getBillingAddress();

        if (!empty($shippingAddress)) {
            $shippingAddress->unsetData('extension_attributes');
            $shippingAddressData = $shippingAddress->getData();
        }

        if (!empty($billingAddress)) {
            $billingAddress->unsetData('extension_attributes');
            $billingAddressData = $billingAddress->getData();
        }

        if ($shippingAddress) {
            $addressDiff = array_diff_assoc($shippingAddressData, $billingAddressData);
            unset($addressDiff['address_type'], $addressDiff['entity_id']);
            $shippingAddress->setSameAsBilling(empty($addressDiff));
        }

        $this->_initBillingAddressFromOrder($order);
        $this->_initShippingAddressFromOrder($order);

        $quote = $this->getQuote();
        if (!$quote->isVirtual() && $this->getShippingAddress()->getSameAsBilling()) {
            $this->setShippingAsBilling(1);
        }

        $this->setShippingMethod($order->getShippingMethod());
        $quote->getShippingAddress()->setShippingDescription($order->getShippingDescription());

        $orderCouponCode = $order->getCouponCode();
        if ($orderCouponCode) {
            $quote->setCouponCode($orderCouponCode);
        }

        if ($quote->getCouponCode()) {
            $quote->collectTotals();
        }

        $this->_objectCopyService->copyFieldsetToTarget('sales_copy_order', 'to_edit', $order, $quote);

        $this->_eventManager->dispatch('sales_convert_order_to_quote', ['order' => $order, 'quote' => $quote]);

        if (!$order->getCustomerId()) {
            $quote->setCustomerIsGuest(true);
        }

        if ($session->getUseOldShippingMethod(true)) {
            /*
             * if we are making reorder or editing old order
             * we need to show old shipping as preselected
             * so for this we need to collect shipping rates
             */
            $this->collectShippingRates();
        } else {
            /*
             * if we are creating new order then we don't need to collect
             * shipping rates before customer hit appropriate button
             */
            $this->collectRates();
        }

        $quote->getShippingAddress()->unsCachedItemsAll();
        $quote->setTotalsCollectedFlag(false);

        $this->quoteRepository->save($quote);

        return $this;
    }

    /**
     * Parse data retrieved from request
     *
     * @param   array $data
     * @return  $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function importPostData($data)
    {
        if (is_array($data)) {
            $this->addData($data);
        } else {
            return $this;
        }

        if (isset($data[CustomAttributes::ORDER_ENTITY])) {
            $orderFields = $data[CustomAttributes::ORDER_ENTITY];
            $this->getQuote()->addData($orderFields);
        }

        if (isset($data['account'])) {
            $this->setAccountData($data['account']);
        }

        if (isset($data['comment'])) {
            $this->getQuote()->addData($data['comment']);
            if (empty($data['comment']['customer_note_notify'])) {
                $this->getQuote()->setCustomerNoteNotify(false);
            } else {
                $this->getQuote()->setCustomerNoteNotify(true);
            }
        }

        if (isset($data['billing_address'])) {
            $this->setBillingAddress($data['billing_address']);
        }

        if (isset($data['shipping_address'])) {
            $this->setShippingAddress($data['shipping_address']);
        }

        if (isset($data['shipping_method'])) {
            $this->setShippingMethod($data['shipping_method']);
        }

        if (isset($data['payment_method'])) {
            $this->setPaymentMethod($data['payment_method']);
        }

        if (isset($data['coupon']['code'])) {
            $this->applyCoupon($data['coupon']['code']);
        }

        return $this;
    }
}
