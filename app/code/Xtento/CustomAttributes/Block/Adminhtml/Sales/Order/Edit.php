<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-01-11T23:10:03+00:00
 * File:          app/code/Xtento/CustomAttributes/Block/Adminhtml/Sales/Order/Edit.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Block\Adminhtml\Sales\Order;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected $_blockGroup = 'Xtento_CustomAttributes';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Edit constructor.
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        parent::_construct();
        $this->removeButton('delete');
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl(
            'sales/order/view', ['order_id' => $this->getOrder()->getId()]
        );
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'xtento_customattributes/order/save', ['_current' => true]
        );
    }
}
