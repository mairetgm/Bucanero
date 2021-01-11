<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\OrderUpdater\Model\ResourceModel\Modules\Actions;

/**
 * Class Ignored
 * @package Wyomind\OrderUpdater\Model\ResourceModel\Type
 */
class ActionCancel extends \Wyomind\OrderUpdater\Model\ResourceModel\Modules\AbstractResource
{
    protected $elements;
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Framework\Model\ResourceModel\Db\Context $context, \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection, $connectionName = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($wyomind, $context, $entityAttributeCollection);
        $elements = [];
        $this->elements = $elements;
    }
    /**
     * List of new mapping attributes
     * @return array
     */
    public function getDropdown()
    {
        /* ATTRIBUTE MAPPING */
        $dropdown = [];
        $dropdown['cancel'] = ['label' => 'Cancel', 'style' => "", 'type' => "None", 'elements' => $this->elements, 'newable' => true];
        return $dropdown;
    }
    /**
     * Execute the module's action on an order
     * @return array
     */
    public function execute($action, &$entity, $preview)
    {
        $result = true;
        $order = $entity['order'];
        $entity['log'][] = ['type' => 'notice', 'message' => __('Trying to cancel order')];
        if ($order->canCancel()) {
            if ($preview != 1) {
                try {
                    $this->orderManagement->cancel($order->getId());
                    $entity['log'][] = ['type' => 'success', 'message' => __('Order successfully canceled')];
                } catch (\Exception $e) {
                    $entity['log'][] = ['type' => 'error', 'message' => __('Order could not be cancelled')];
                    $result = false;
                }
            }
        } else {
            $entity['log'][] = ['type' => 'error', 'message' => __('Order is not cancellable')];
            $result = false;
        }
        return $result;
    }
}