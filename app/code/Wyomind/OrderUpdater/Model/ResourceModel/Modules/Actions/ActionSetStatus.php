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
class ActionSetStatus extends \Wyomind\OrderUpdater\Model\ResourceModel\Modules\AbstractResource
{
    protected $elements;
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Framework\Model\ResourceModel\Db\Context $context, \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection, $connectionName = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($wyomind, $context, $entityAttributeCollection);
        $elements = [];
        $elements[0]['type'] = "select";
        $elements[0]['label'] = __("Select the status");
        $elements[0]['groups']['fixed']['name'] = "Fixed value";
        $elements[0]['groups']['fixed']['values'] = [];
        foreach ($this->orderConfig->getStates() as $stateKey => $stateLabel) {
            foreach ($this->orderConfig->getStateStatuses($stateKey) as $statusKey => $statusLabel) {
                $elements[0]['groups']['fixed']['values'][$statusKey] = $statusLabel;
            }
        }
        // Add file elements
        $fileHeader = $this->helperData->getFileHeader();
        if (count($fileHeader)) {
            $elements[0]['groups']['file']['name'] = "File value";
            $elements[0]['groups']['file']['values'] = [];
            foreach ($fileHeader as $headerKey => $headerLabel) {
                $elements[0]['groups']['file']['values']['_file_' . $headerKey] = $headerLabel;
            }
        }
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
        $dropdown['set_status'] = ['label' => 'Set status', 'style' => "", 'type' => "Option value name (case sensitive)", 'elements' => $this->elements, 'newable' => true];
        return $dropdown;
    }
    /**
     * Execute the module's action on an order
     * @return array
     */
    public function execute($action, &$entity, $preview)
    {
        $result = true;
        // @TODO limit the action to existing statuses? Limit it to existing statuses corresponding to the current status of the order?
        $order = $entity['order'];
        $targetStatus = '';
        if (strpos($action->{'action-option-1'}, '_file_') === 0) {
            $fileIndex = substr($action->{'action-option-1'}, 6);
            $targetStatus = $entity[$fileIndex];
        } else {
            $targetStatus = $action->{'action-option-1'};
        }
        try {
            if ($action->{'action-option-1-script'} != '') {
                $targetStatus = $this->helperData->execPhp($action->{'action-option-1-script'}, $targetStatus, $entity);
            }
            $entity['log'][] = ['type' => 'notice', 'message' => __('Trying to set status to order: %1', $targetStatus)];
            if ($preview != 1) {
                $history = $order->addStatusHistoryComment('', $targetStatus);
                $history->setIsVisibleOnFront(false);
                $history->setIsCustomerNotified(false);
                $history->save();
                $order->save();
                $entity['log'][] = ['type' => 'success', 'message' => __('Order status successfully affected')];
            }
        } catch (\Exception $e) {
            $entity['log'][] = ['type' => 'error', 'message' => __('Error while setting the status:') . nl2br(htmlentities($e->getMessage()))];
            $result = false;
        }
        return $result;
    }
}