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
class ActionInvoice extends \Wyomind\OrderUpdater\Model\ResourceModel\Modules\AbstractResource
{
    protected $elements;
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Framework\Model\ResourceModel\Db\Context $context, \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection, $connectionName = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($wyomind, $context, $entityAttributeCollection);
        $elements = [];
        // Add file elements
        $fileHeader = $this->helperData->getFileHeader();
        // Send invoice email
        $elements[0]['type'] = "select";
        $elements[0]['label'] = __("Send invoice email");
        if (count($fileHeader)) {
            $elements[0]['groups']['fixed']['name'] = __("Fixed value");
            $elements[0]['groups']['fixed']['values'] = [0];
            $elements[0]['groups']['fixed']['values'][1] = __("Yes");
            $elements[0]['groups']['fixed']['values'][0] = __("No");
            $elements[0]['groups']['file']['name'] = __("File value");
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
        $dropdown['invoice'] = ['label' => 'Invoice', 'style' => "", 'type' => "None", 'elements' => $this->elements, 'newable' => true];
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
        $sendMail = false;
        if (strpos($action->{'action-option-1'}, '_file_') === 0) {
            $fileIndex = substr($action->{'action-option-1'}, 6);
            $sendMail = $entity[$fileIndex];
        } else {
            $sendMail = $action->{'action-option-1'};
        }
        $noticeMessage = '';
        if ($sendMail) {
            $noticeMessage .= __(' and send invoice email');
        }
        $entity['log'][] = ['type' => 'notice', 'message' => __('Trying to invoice order') . $noticeMessage];
        if ($order->canInvoice()) {
            try {
                if ($preview != 1) {
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->register();
                    $invoice->save();
                    $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
                    $transactionSave->save();
                    if ($sendMail) {
                        $this->invoiceSender->send($invoice);
                    }
                    $entity['log'][] = ['type' => 'success', 'message' => __('Order successfully invoiced')];
                }
            } catch (\Exception $e) {
                $entity['log'][] = ['type' => 'error', 'message' => __('There was an error when trying to create invoice for order: %1', $e->getMessage())];
                $result = false;
            }
        } else {
            $entity['log'][] = ['type' => 'error', 'message' => __('Order is not invoicable')];
            $result = false;
        }
        return $result;
    }
}