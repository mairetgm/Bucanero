<?php

/**
 * Product:       Xtento_CustomAttributes
 * ID:            SI4Hun9m/xiQVsUoazSzL/FctvYBoSsCO3VpsqowTZI=
 * Last Modified: 2019-12-14T14:08:10+00:00
 * File:          app/code/Xtento/CustomAttributes/Controller/Adminhtml/Order/Save.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\CustomAttributes\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Xtento\CustomAttributes\Api\Data\FieldsInterface;
use Xtento\CustomAttributes\Block\Adminhtml\Sales\Order\Create\OrderAttributes;
use Xtento\CustomAttributes\Helper\Data;
use Xtento\CustomAttributes\Model\CustomAttributes;
use Xtento\CustomAttributes\Model\Sources\InputType;
use Xtento\CustomAttributes\Model\FileUpload;

class Save extends Action
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
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FileUpload
     */
    protected $fileUpload;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $dataHelper
     * @param FilterBuilder $filterBuilder
     * @param FileUpload $fileUpload
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        OrderRepositoryInterface $orderRepository,
        Data $dataHelper,
        FilterBuilder $filterBuilder,
        FileUpload $fileUpload
    ) {
        $this->registry = $coreRegistry;
        $this->orderRepository = $orderRepository;
        $this->filterBuilder = $filterBuilder;
        $this->dataHelper = $dataHelper;
        $this->fileUpload = $fileUpload;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($id);
        $this->registry->register('current_order', $order);
        $data = $this->getRequest()->getPost('order');
        $files = $this->getRequest()->getFiles()->toArray();
        if (!isset($data['order_field']) && !isset($files['order'])) {
            $this->messageManager->addWarningMessage(__('No attribute data found in POST request. Blocked by your servers firewall maybe?'));
            return $resultRedirect->setPath(
                'sales/order/view',
                ['order_id' => $order->getId(), '_current' => true]
            );
        }

        $attributeData = $data['order_field'];
        if (array_key_exists('order', $files) && array_key_exists('order_field', $files['order'])) {
            $attributeData = array_merge($attributeData, $files['order']['order_field']);
        }

        try {
            $editableFields = $this->getFields();
            $fileUploadNameMapping = [];
            $fieldsUpdated = false;
            foreach ($editableFields as $editableField) {
                $attribute = $editableField->getData(Data::ATTRIBUTE_DATA);
                if (isset($attributeData[$attribute->getAttributeCode()])) {
                    $attributeValue = $attributeData[$attribute->getAttributeCode()];
                    if ($attribute->getFrontendInput() === InputType::MULTI_SELECT) {
                        $attributeValue = implode(",", $attributeValue);
                    }
                    if ($attribute->getFrontendInput() === InputType::FILE && isset($attributeValue['name'])) {
                        $origFilename = $attributeValue['name'];
                        $attributeValue = uniqid() . '_' . $origFilename;
                        $fileUploadNameMapping[$origFilename] = $attributeValue;
                    }
                    $order->setData($attribute->getAttributeCode(), $attributeValue);
                    $fieldsUpdated = true;
                }
            }
            if (array_key_exists('order', $files) && array_key_exists('order_field', $files['order'])) {
                $this->fileUpload->adminUploads($files['order']['order_field'], $fileUploadNameMapping);
            }
            if ($fieldsUpdated) {
                $this->orderRepository->save($order);
            }
            $this->messageManager->addSuccessMessage(__('Custom attributes have been updated successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('There was an error saving your custom attributes: %1', $e->getMessage())
            );
        }

        return $resultRedirect->setPath(
            'sales/order/view',
            ['order_id' => $order->getId(), '_current' => true]
        );
    }

    protected function getFields()
    {
        $filters = [
            $this->filterBuilder
                ->setField(FieldsInterface::ATTRIBUTE_TYPE)
                ->setValue(CustomAttributes::ORDER_ENTITY)
                ->create()
        ];

        $fields = $this->dataHelper->createFields($filters, OrderAttributes::ADMIN_ORDER_LOCATION);

        if (empty($fields[CustomAttributes::ORDER_ENTITY])) {
            return [];
        }

        return $fields[CustomAttributes::ORDER_ENTITY];
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ACTION);
    }
}
