<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrdersExportTool\Block\Adminhtml\Order;

/**
 * Render the export button in order > view
 */
class View
{
    public function __construct(\Wyomind\OrdersExportTool\Helper\Delegate $wyomind)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
    }
    /**
     * Interceptor for getOrder
     * @param \Magento\Sales\Block\Adminhtml\Order\View $subject
     */
    public function beforeGetOrder(\Magento\Sales\Block\Adminhtml\Order\View $subject)
    {
        $subject->addButton('void_payment', ['label' => __('Export'), 'onclick' => 'setLocation(\'' . $this->_urlInterface->getUrl('ordersexporttool/orders/export', ['order_ids' => $subject->getRequest()->getParam('order_id'), "profile_ids" => false]) . '\')']);
    }
}