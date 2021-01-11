<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-12T17:30:34+00:00
 * File:          app/code/Xtento/CustomAttributes/Plugin/Sales/Model/AdminOrder/CreatePlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Plugin\Sales\Model\AdminOrder;

use Magento\Sales\Model\Order;

class CreatePlugin
{
    public function aroundInitFromOrder(
        $subject,
        \Closure $proceed,
        Order $order
    ) {
        /** @var Order\Address $billingAddress */
        $billingAddress = $order->getBillingAddress();

        $billingAddress->unsetData();
        $order->setBillingAddress($billingAddress);
        $order->setShippingAddress($billingAddress);

        $proceed($order);
    }
}
