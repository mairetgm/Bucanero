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
class ActionCreditmemo extends \Wyomind\OrderUpdater\Model\ResourceModel\Modules\AbstractResource
{
    protected $elements;
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Framework\Model\ResourceModel\Db\Context $context, \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection, $connectionName = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($wyomind, $context, $entityAttributeCollection);
        $elements = [];
        // Add file elements
        $fileHeader = $this->helperData->getFileHeader();
        // Send creditmemo email
        $elements[0]['type'] = "select";
        $elements[0]['label'] = __("Send creditmemo email");
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
        $dropdown['creditmemo'] = ['label' => 'Creditmemo', 'style' => "", 'type' => "None", 'elements' => $this->elements, 'newable' => true];
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
            $noticeMessage .= __(' and send creditmemo email');
        }
        $entity['log'][] = ['type' => 'notice', 'message' => __('Trying to create a creditmemo on order') . $noticeMessage];
        if ($order->canCreditmemo()) {
            if ($preview != 1) {
                try {
                    $invoices = $order->getInvoiceCollection();
                    foreach ($invoices as $invoice) {
                        $invoiceincrementid = $invoice->getIncrementId();
                    }
                    $invoiceobj = $this->invoice->loadByIncrementId($invoiceincrementid);
                    // use last invoice as a reference for the creditmemo
                    $creditmemo = $this->creditmemoFactory->createByOrder($order);
                    // @todo Don't set invoice if you want to do offline refund
                    $creditmemo->setInvoice($invoiceobj);
                    $this->creditmemoService->refund($creditmemo);
                    if ($sendMail) {
                        $this->creditmemoSender->send($creditmemo);
                    }
                    $entity['log'][] = ['type' => 'success', 'message' => __('Creditmemo successfully created for order')];
                } catch (\Exception $e) {
                    $entity['log'][] = ['type' => 'error', 'message' => __('There was an error when trying to create creditmemo for order: %1', $e->getMessage())];
                    $result = false;
                }
            }
        } else {
            $entity['log'][] = ['type' => 'error', 'message' => __('Creditmemo can\'t be created for order')];
            $result = false;
        }
        return $result;
    }
}