<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:03+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Order/LastOrderData.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Order;

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Backend\Model\Session\Quote as BackendQuote;

class LastOrderData
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CollectionFactory
     */
    private $orderCollection;

    /**
     * @var OrderCollection
     */
    private $orders;

    /**
     * @var BackendQuote
     */
    private $backendQuote;

    public function __construct(
        Session $checkoutSession,
        CollectionFactory $orderCollection,
        BackendQuote $backendQuote
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->orderCollection = $orderCollection;
        $this->backendQuote = $backendQuote;
    }

    public function getOrderData()
    {
        $customerId = $this->checkoutSession->getQuote()->getCustomerId();
        $backendQuote = $this->backendQuote;
        if ($backendQuote->getCustomerId()) {
            $customerId = $backendQuote->getCustomerId();
        }

        if ($customerId === null) {
            return false;
        }

        if (!$this->orders) {
            $this->orders = $this->orderCollection->create($customerId);
        }

        $order = $this->orders->getLastItem();

        if ($order instanceof Order) {
            return $order;
        }

        return false;
    }
}