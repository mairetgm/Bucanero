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
class ActionAddComment extends \Wyomind\OrderUpdater\Model\ResourceModel\Modules\AbstractResource
{
    protected $elements;
    public function __construct(\Wyomind\OrderUpdater\Helper\Delegate $wyomind, \Magento\Framework\Model\ResourceModel\Db\Context $context, \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection, $connectionName = null)
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($wyomind, $context, $entityAttributeCollection);
        $elements = [];
        $elements[0]['type'] = "select";
        $elements[0]['label'] = __("Comment");
        $elements[0]['groups']['fixed']['name'] = __("Fixed value");
        $elements[0]['groups']['fixed']['values'] = [];
        $elements[0]['groups']['fixed']['values']['_custom_'] = __("Custom value");
        // Add file elements
        $fileHeader = $this->helperData->getFileHeader();
        if (count($fileHeader)) {
            $elements[0]['groups']['file']['name'] = __("File value");
            $elements[0]['groups']['file']['values'] = [];
            foreach ($fileHeader as $headerKey => $headerLabel) {
                $elements[0]['groups']['file']['values']['_file_' . $headerKey] = $headerLabel;
            }
        }
        // Send comment email
        $elements[1]['type'] = "select";
        $elements[1]['label'] = __("Send comment email");
        if (count($fileHeader)) {
            $elements[1]['groups']['fixed']['name'] = __("Fixed value");
            $elements[1]['groups']['fixed']['values'] = [0];
            $elements[1]['groups']['fixed']['values'][1] = __("Yes");
            $elements[1]['groups']['fixed']['values'][0] = __("No");
            $elements[1]['groups']['file']['name'] = __("File value");
            $elements[1]['groups']['file']['values'] = [];
            foreach ($fileHeader as $headerKey => $headerLabel) {
                $elements[1]['groups']['file']['values']['_file_' . $headerKey] = $headerLabel;
            }
        }
        // Make comment visible on front
        $elements[2]['type'] = "select";
        $elements[2]['label'] = __("Make visible on front");
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
        $dropdown['add_comment'] = ['label' => 'Add comment', 'style' => "", 'type' => "Option value name (case sensitive)", 'elements' => $this->elements, 'newable' => true];
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
        $comment = '';
        if (strpos($action->{'action-option-1'}, '_file_') === 0) {
            $fileIndex = substr($action->{'action-option-1'}, 6);
            $comment = $entity[$fileIndex];
        } elseif ($action->{'action-option-1'} == '_custom_') {
            $comment = $action->{'action-option-1-custom'};
        }
        $sendMail = false;
        if (strpos($action->{'action-option-2'}, '_file_') === 0) {
            $fileIndex = substr($action->{'action-option-2'}, 6);
            $sendMail = $entity[$fileIndex];
        } else {
            $sendMail = $action->{'action-option-2'};
        }
        $visible = false;
        if (strpos($action->{'action-option-3'}, '_file_') === 0) {
            $fileIndex = substr($action->{'action-option-3'}, 6);
            $visible = $entity[$fileIndex];
        } else {
            $visible = $action->{'action-option-3'};
        }
        try {
            if ($action->{'action-option-1-script'} != '') {
                $comment = $this->helperData->execPhp($action->{'action-option-1-script'}, $comment, $entity);
            }
            if ($action->{'action-option-2-script'} != '') {
                $sendMail = $this->helperData->execPhp($action->{'action-option-2-script'}, $sendMail, $entity);
            }
            if ($action->{'action-option-3-script'} != '') {
                $visible = $this->helperData->execPhp($action->{'action-option-3-script'}, $visible, $entity);
            }
            $noticeMessage = '';
            if ($sendMail) {
                $noticeMessage .= __(' and send comment email');
            }
            if ($visible) {
                $entity['log'][] = ['type' => 'notice', 'message' => __('Trying to add comment visible in frontend: \'%1\'', substr($comment, 0, 50)) . $noticeMessage, 'longmessage' => __('Trying to add comment visible in frontend: %1', $comment) . $noticeMessage];
            } else {
                $entity['log'][] = ['type' => 'notice', 'message' => __('Trying to add comment not visible in frontend: \'%1\'', substr($comment, 0, 50)) . $noticeMessage, 'longmessage' => __('Trying to add comment not visible in frontend: %1', $comment) . $noticeMessage];
            }
            if ($preview != 1) {
                if ($comment != '') {
                    $history = $order->addStatusHistoryComment($comment);
                    $history->setIsVisibleOnFront($visible);
                    $history->setIsCustomerNotified($sendMail);
                    $history->save();
                    $entity['log'][] = ['type' => 'success', 'message' => __('Comment successfully added')];
                } else {
                    $entity['log'][] = ['type' => 'warning', 'message' => __('Unable to add an empty comment')];
                    $result = false;
                }
                $order->save();
            }
        } catch (\Exception $e) {
            $entity['log'][] = ['type' => 'error', 'message' => __('Error while adding the comment:') . nl2br(htmlentities($e->getMessage()))];
            $result = false;
        }
        return $result;
    }
}