<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:03+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Index/DelegateCreate.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Xtento\CustomAttributes\Model\Order\OrderCustomerDelegate;

/**
 * Class DelegateCreate
 * @package Xtento\CustomAttributes\Controller\Index
 */
class DelegateCreate extends Action
{
    /**
     * @var OrderCustomerDelegate
     */
    private $delegateService;

    /**
     * @var Session
     */
    private $session;

    /**
     * DelegateCreate constructor.
     *
     * @param Context $context
     * @param OrderCustomerDelegate $customerDelegation
     * @param Session $session
     */
    public function __construct(
        Context $context,
        OrderCustomerDelegate $customerDelegation,
        Session $session
    ) {
        parent::__construct($context);
        $this->delegateService = $customerDelegation;
        $this->session = $session;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = $this->session->getLastOrderId();
        if (!$orderId) {
            return $this->resultRedirectFactory->create()->setPath('/');
        }

        return $this->delegateService->delegateNew((int)$orderId);
    }
}
