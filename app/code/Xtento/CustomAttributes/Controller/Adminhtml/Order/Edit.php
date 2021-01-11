<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-12-14T13:00:23+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Adminhtml/Order/Edit.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Action
{
    const ACTION = 'Magento_Sales::actions_view';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Edit constructor.
     *
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->registry = $coreRegistry;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->messageManager->addWarningMessage(__('You can only edit order attributes here. To edit customer/customer address attributes, edit the customer directly.'));
        $id = $this->getRequest()->getParam('order_id');
        $currentOrder = $this->orderRepository->get($id);
        $this->registry->register('current_order', $currentOrder);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(
            __('Edit Custom Attributes - Order #%1', $currentOrder->getIncrementId())
        );

        return $resultPage;
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ACTION);
    }
}
