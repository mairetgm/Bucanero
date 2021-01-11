<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:03+00:00
 * File:          app/code/Xtento/CustomAttributes/Model/Order/OrderCustomerDelegate.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Model\Order;

use Xtento\CustomAttributes\Model\AccountDelegationFactory;

class OrderCustomerDelegate
{
    /**
     * @var OrderCustomerExtractor
     */
    private $customerExtractor;

    /**
     * @var AccountDelegationFactory
     */
    private $delegateFactory;

    /**
     * OrderCustomerDelegate constructor.
     *
     * @param OrderCustomerExtractor $customerExtractor
     * @param AccountDelegationFactory $delegateFactory
     */
    public function __construct(
        OrderCustomerExtractor $customerExtractor,
        AccountDelegationFactory $delegateFactory
    ) {
        $this->customerExtractor = $customerExtractor;
        $this->delegateFactory = $delegateFactory;
    }

    /**
     * @inheritDoc
     */
    public function delegateNew(int $orderId)
    {
        $delegateFactory = $this->delegateFactory->create();

        if ($delegateFactory) {
            return $delegateFactory->createRedirectForNew(
                $this->customerExtractor->extract($orderId),
                ['__sales_assign_order_id' => $orderId]
            );
        }
    }
}
