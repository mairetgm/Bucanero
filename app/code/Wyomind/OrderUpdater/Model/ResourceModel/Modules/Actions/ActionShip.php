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
class ActionShip extends \Wyomind\OrderUpdater\Model\ResourceModel\Modules\AbstractResource
{
    /** @var \Magento\Inventory\Model\ResourceModel\Source\Collection */
    protected $sourceFactory;
    protected $carriers;
    protected $elements;
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Framework\Model\ResourceModel\Db\Context $context, \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection, $connectionName = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($wyomind, $context, $entityAttributeCollection);
        // get carriers
        $carriers = ['custom' => 'Custom Value'];
        $carrierInstances = $this->shippingConfig->getAllCarriers();
        foreach ($carrierInstances as $carrierCode => $carrierModel) {
            if ($carrierModel->isTrackingAvailable()) {
                $carriers[$carrierCode] = $carrierModel->getConfigData('title');
            }
        }
        $this->carriers = $carriers;
        $elements = [];
        $elements[0]['type'] = "select";
        $elements[0]['label'] = __("Carrier");
        $elements[0]['groups']['fixed']['name'] = __("Fixed value");
        $elements[0]['groups']['fixed']['values'] = [];
        foreach ($this->carriers as $key => $value) {
            $elements[0]['groups']['fixed']['values'][$key] = __($value);
        }
        // Add file elements
        $fileHeader = $this->helperData->getFileHeader();
        if (count($fileHeader)) {
            $elements[0]['groups']['file']['name'] = __("File value");
            $elements[0]['groups']['file']['values'] = [];
            foreach ($fileHeader as $headerKey => $headerLabel) {
                $elements[0]['groups']['file']['values']['_file_' . $headerKey] = $headerLabel;
            }
        }
        $elements[1]['type'] = "select";
        $elements[1]['label'] = __("Tracking number");
        // Add file elements
        $fileHeader = $this->helperData->getFileHeader();
        if (count($fileHeader)) {
            $elements[1]['groups']['file']['name'] = __("File value");
            $elements[1]['groups']['file']['values'] = [];
            foreach ($fileHeader as $headerKey => $headerLabel) {
                $elements[1]['groups']['file']['values']['_file_' . $headerKey] = $headerLabel;
            }
        }
        // Send shipping email
        $elements[2]['type'] = "select";
        $elements[2]['label'] = __("Send shipping email");
        if (count($fileHeader)) {
            $elements[2]['groups']['fixed']['name'] = __("Fixed value");
            $elements[2]['groups']['fixed']['values'] = [0];
            $elements[2]['groups']['fixed']['values'][1] = __("Yes");
            $elements[2]['groups']['fixed']['values'][0] = __("No");
            $elements[2]['groups']['file']['name'] = __("File value");
            $elements[2]['groups']['file']['values'] = [];
            foreach ($fileHeader as $headerKey => $headerLabel) {
                $elements[2]['groups']['file']['values']['_file_' . $headerKey] = $headerLabel;
            }
        }
        if ($this->helperData->isMsiEnabled()) {
            $this->sourceFactory = $this->objectManager->create('\\Magento\\Inventory\\Model\\ResourceModel\\Source\\CollectionFactory');
            $sources = $this->sourceFactory->create();
            $elements[3]['type'] = "select";
            $elements[3]['label'] = __("Source to use");
            $elements[3]['groups']['fixed']['name'] = __("Fixed value");
            $elements[3]['groups']['fixed']['values'] = [];
            foreach ($sources as $source) {
                $elements[3]['groups']['fixed']['values'][$source->getSourceCode()] = $source->getName();
            }
            // Add file elements
            $fileHeader = $this->helperData->getFileHeader();
            if (count($fileHeader)) {
                $elements[3]['groups']['file']['name'] = __("File value");
                $elements[3]['groups']['file']['values'] = [];
                foreach ($fileHeader as $headerKey => $headerLabel) {
                    $elements[3]['groups']['file']['values']['_file_' . $headerKey] = $headerLabel;
                }
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
        $dropdown['ship'] = ['label' => 'Ship', 'style' => "", 'type' => "None", 'elements' => $this->elements, 'newable' => true];
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
        $carrierCode = '';
        if (strpos($action->{'action-option-1'}, '_file_') === 0) {
            $fileIndex = substr($action->{'action-option-1'}, 6);
            $carrierCode = $entity[$fileIndex];
        } else {
            $carrierCode = $action->{'action-option-1'};
        }
        $trackingNumber = '';
        if (strpos($action->{'action-option-2'}, '_file_') === 0) {
            $fileIndex = substr($action->{'action-option-2'}, 6);
            $trackingNumber = $entity[$fileIndex];
        }
        $notifyCustomer = '';
        if (strpos($action->{'action-option-3'}, '_file_') === 0) {
            $fileIndex = substr($action->{'action-option-3'}, 6);
            $notifyCustomer = $entity[$fileIndex];
        } else {
            $notifyCustomer = $action->{'action-option-3'};
        }
        if (strpos($action->{'action-option-4'}, '_file_') === 0) {
            $fileIndex = substr($action->{'action-option-4'}, 6);
            $sourceCode = $entity[$fileIndex];
        } else {
            $sourceCode = $action->{'action-option-4'};
        }
        try {
            if ($action->{'action-option-1-script'} != '') {
                $carrierCode = $this->helperData->execPhp($action->{'action-option-1-script'}, $carrierCode, $entity);
            }
            if ($action->{'action-option-2-script'} != '') {
                $trackingNumber = $this->helperData->execPhp($action->{'action-option-2-script'}, $trackingNumber, $entity);
            }
            if ($action->{'action-option-3-script'} != '') {
                $notifyCustomer = $this->helperData->execPhp($action->{'action-option-3-script'}, $notifyCustomer, $entity);
            }
            if ($action->{'action-option-4-script'} != '') {
                $sourceCode = $this->helperData->execPhp($action->{'action-option-4-script'}, $sourceCode, $entity);
            }
            $noticeMessage = __('Trying to ship order with carrier %1 and tracking number %2', $carrierCode, $trackingNumber);
            if ($sourceCode) {
                $noticeMessage .= __(' on source %1', $sourceCode);
            }
            if ($notifyCustomer) {
                $noticeMessage .= __(' and send shipping email');
            }
            $entity['log'][] = ['type' => 'notice', 'message' => $noticeMessage];
            // to check order can ship or not
            if ($order->canShip()) {
                if ($preview != 1) {
                    $orderShipment = $this->convertOrder->toShipment($order);
                    if ($this->helperData->isMsiEnabled()) {
                        $orderShipment->getExtensionAttributes()->setSourceCode($sourceCode);
                    }
                    foreach ($order->getAllItems() as $orderItem) {
                        // Check virtual item and item Quantity
                        if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                            continue;
                        }
                        $qty = $orderItem->getQtyToShip();
                        $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qty);
                        $orderShipment->addItem($shipmentItem);
                    }
                    /*Add tracking information*/
                    $data = array('carrier_code' => $carrierCode, 'title' => $this->carriers[$carrierCode], 'number' => $trackingNumber);
                    $track = $this->trackFactory->create()->addData($data);
                    $orderShipment->addTrack($track);
                    $orderShipment->register();
                    $orderShipment->getOrder()->setIsInProcess(true);
                    try {
                        // Save created Order Shipment
                        $orderShipment->save();
                        $orderShipment->getOrder()->save();
                        // Send Shipment Email
                        if ($notifyCustomer) {
                            $this->shipmentNotifier->notify($orderShipment);
                        }
                        $orderShipment->save();
                        $entity['log'][] = ['type' => 'success', 'message' => __('Order successfully shipped')];
                    } catch (\Exception $e) {
                        $entity['log'][] = ['type' => 'error', 'message' => __('Order could not be shipped'), 'longmessage' => __('Order could not be shipped: %1', $e->getMessage())];
                        $result = false;
                    }
                }
            } else {
                $entity['log'][] = ['type' => 'error', 'message' => __('Order is not shippable')];
                $result = false;
            }
        } catch (\Exception $e) {
            $entity['log'][] = ['type' => 'error', 'message' => __('Error while shipping the order:') . nl2br(htmlentities($e->getMessage()))];
            $result = false;
        }
        return $result;
    }
}